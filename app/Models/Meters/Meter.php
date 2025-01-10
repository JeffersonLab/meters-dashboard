<?php

namespace App\Models\Meters;

use App\Exceptions\MeterDataException;
use App\Exceptions\ModelValidationException;
use App\Models\BaseModel;
use App\Models\Buildings\Building;
use App\Models\DataTables\DataTableCreator;
use App\Models\DataTables\DataTableInterface;
use App\Models\DataTables\DataTableReporter;
use App\Models\DataTables\DataTableTrait;
use App\Presenters\GasMeterPresenter;
use App\Presenters\PowerMeterPresenter;
use App\Presenters\WaterMeterPresenter;
use App\Utilities\MySampler;
use App\Utilities\MySamplerData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Robbo\Presenter\PresentableInterface;

class Meter extends BaseModel implements DataTableInterface, PresentableInterface
{
    use DataTableTrait;
    use SoftDeletes;   //also see https://www.honeybadger.io/blog/a-guide-to-soft-deletes-in-laravel/

    protected $table = 'meters';

    public static $rules = [
        'name' => 'required | max:80',
        'type' => 'required | in:power,water,gas',
        'epics_name' => 'max:40',
        'name_alias' => 'max:80',
    ];

    public $fillable = [
        'name',
        'type',
        'building_id',
        'epics_name',
        'name_alias',
        'model_number',
        'housed_by',
        'begins_at',
    ];

    protected $reporter;

    /**
     * Meter constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->dataTableFk = 'meter_id';
        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'begins_at' => 'datetime',
        ];
    }

    public static function typeFromCEDType($cedType)
    {
        switch (strtolower($cedType)) {
            case 'powermeter':
                return 'power';
            case 'watermeter':
                return 'water';
            case 'gasmeter':
                return 'gas';
        }

        return null;
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function findByPv($pv)
    {
        return Meter::fromPv($pv)->first();  // epics_name must be unique
    }

    public function scopeFromPv($query, $pv)
    {
        return $query->where('epics_name', '=', self::epicsNameFromPv($pv));
    }

    public function meterLimits(): HasMany
    {
        return $this->hasMany(MeterLimit::class);
    }

    public function rolloverEvents(): HasMany
    {
        return $this->hasMany(RolloverEvent::class)->orderBy('rollover_at');
    }

    public function fieldLimits($field)
    {
        // we use first b/c we rely on database uniqueness to
        // ensure only one of each field value per meter.
        return $this->meterLimits->where('field', $field)->first();
    }

    public function hasMeterLimits($field = null)
    {
        if ($field) {
            return ! $this->meterLimits->where('field', $field)->isEmpty();
        }

        return ! $this->meterLimits->isEmpty();
    }

    public function lastRolloverEvent($field)
    {
        return $this->rolloverEvents()->where('field', $field)->get()->last();
    }

    /**
     * Examines the data table to discover if any rollovers (aka overflows)
     * have occurred in the fields that are susceptible to rollover,
     * and if so, creates and stores new RolloverEvents that document them.
     *
     * @throws ModelValidationException
     */
    public function makeNewRolloverEvents()
    {
        $eventsMade = 0;
        foreach ($this->rolloverFields() as $field) {
            // Obtain the date from which to start scanning forward
            // for a rollover event.
            $priorRollover = $this->lastRolloverEvent($field);
            if ($priorRollover) {
                $startDate = $priorRollover->rollover_at;
                $accumulatedRollover = $priorRollover->rollover_accumulated;
            } else {
                $startDate = $this->begins_at;
                $accumulatedRollover = 0;
            }
            //var_dump($startDate);
            // Get the data that follows the date
            $query = $this->dataTable()->select('*')
                ->where('meter_id', $this->id)
                ->whereNotNull($field)
                ->where('date', '>', $startDate)->orderBy('date');
            //var_dump($query->toSql());
            $data = $query->get();

            /*
             * Rollover Detection procedure
             *
             *   We want to see the value of the field drop and then climb again, albeit
             *   from the new low starting point.
             *
             *   Plan:
             *
             *   1) Obtain next three consecutive rows ($row[0],[$row[1],$row[2])
             *   2) A rollover occurred at the middle value if all of the following:
             *      a) $row[1] < $row[0]
             *      b) $row[2] < $row[0]
             *      c) $row[2] > $row[1]
             *     where the checks b and c are to guard against a momentary blip after
             *     which the normal count up resumed.               *
             */
            for ($i = 0; $i < $data->count() - 2; $i++) {
                $rows = $data->slice($i, 3);

                // First normalize the data by removing any previously applied
                // rollover.
                for ($j = $i; $j <= $i + 2; $j++) {
                    $rows[$j]->$field = $rows[$j]->$field - $rows[$j]->rollover_accumulated;
                }

                // Apply the logical tests
                if ($rows[$i + 1]->$field < $rows[$i]->$field) {
                    //var_dump($rows);
                    if ($rows[$i + 2]->$field < $rows[$i]->$field
                        && $rows[$i + 2]->$field > $rows[$i + 1]->$field) {
                        $accumulatedRollover += $this->rolloverIncrement($field);
                        $event = new RolloverEvent;
                        $event->meter_id = $this->id;
                        $event->rollover_at = $rows[$i + 1]->date;
                        $event->field = $field;
                        $event->rollover_accumulated = $accumulatedRollover;
                        //var_dump($event);
                        $event->saveOrFail();
                        $eventsMade++;
                    }
                }
            }
        }

        return $eventsMade;
    }

    public function save(array $options = [])
    {
        $saved = parent::save($options);
        // Note that the presence if this DDL here means that save can't be called inside of a transaction
        // because DDL statements implicitly close open transactions.  Calling this inside a transaction or
        // inside saveOrFail (which creates a transaction to call save) will lead to error
        // "There is no active transaction" because the DDL below closed it.
        if ($this->wasRecentlyCreated) {
            (new DataTableCreator($this))->createTable();
        }

        return $saved;
    }


    public function delete()
    {
        if ($this->isForceDeleting()){
           $this->dropDataTable();
        }
        return parent::delete();
    }

    public function forceDelete(){
        $this->dropDataTable();
        return parent::forceDelete();
    }

    protected function dropDataTable() {
        (new DataTableCreator($this))->dropTable();
    }

    /**
     * @return mixed
     *
     * @TODO recalculate the totMBTU column too (update power_meter_data set
     *     totMBTU = totkWh * 0.00341214)
     *
     * @throws \Exception
     */
    protected function applyRollover(string $field, float $accumulatedRollover, Carbon $fromDate, Carbon $toDate)
    {
        $updated = $this->dataTable()->select('*')
            ->where('meter_id', $this->id)
            ->whereNull('rollover_accumulated')
            ->where('date', '>=', $fromDate)
            ->where('date', '<', $toDate)
            ->update(['rollover_accumulated' => $accumulatedRollover,
                $field => DB::raw("$field + $accumulatedRollover"),
            ]);

        return $updated;
    }

    /**
     * Updates the data table so that perpetually incrementing fields do in
     * fact
     * increment perpetually by removing the effect of rollover events that
     * have happened.
     */
    public function applyRolloverEvents()
    {
        $rowsUpdated = 0;
        for ($i = 0; $i < $this->rolloverEvents->count(); $i++) {
            $rolloverToApply = $this->rolloverEvents->slice($i, 1)->first();
            if ($i < $this->rolloverEvents->count() - 1) {
                $stopAt = $this->rolloverEvents->slice($i + 1, 1)->first()->rollover_at;
            } else {
                $stopAt = Carbon::now();
            }
            $rowsUpdated += $this->applyRollover($rolloverToApply->field, $rolloverToApply->rollover_accumulated, $rolloverToApply->rollover_at, $stopAt);
        }

        return $rowsUpdated;
    }

    /**
     * Answers whether the provided value is within the limits for the
     * specified field.
     *
     * @param  mixed  $value  numeric value
     */
    public function withinLimits(string $field, $value): bool
    {
        if (! $this->hasMeterLimits($field)) {
            return true;
        }
        $limits = $this->fieldLimits($field);

        return $limits->isWithinLimits($value);
    }

    public function isTooHigh($field, $value)
    {
        $limits = $this->fieldLimits($field);
        if ($limits) {
            return $limits->isTooHigh($value);
        }

        return false;
    }

    public function isTooLow($field, $value)
    {
        $limits = $this->fieldLimits($field);
        if ($limits) {
            return $limits->isTooLow($value);
        }

        return false;
    }

    /**
     * Returns the EpicsName portion of a pv string by stripping
     * off the known field name.  Returns null if the pv string
     * did not end in a known field.
     *
     * @return bool|null|string
     */
    public static function epicsNameFromPv($pv)
    {
        /*
         * Hunt through all of the fields that can be appended to epics name
         * as per configuration.  If one of them is found on the end of the pv string
         * strip it off and return the remainder which must be the epics_name candidate.
         */
        foreach (self::allPvFields() as $field) {
            if ($field == substr($pv, -1 * strlen($field))) {
                return substr($pv, 0, strlen($pv) - strlen($field));
            }
        }

        return null;
    }

    /**
     * Returns all epics field names defined by configuration.
     */
    public static function allPvFields(): array
    {
        $fields = [];
        $types = array_keys(config('meters.pvs'));
        foreach ($types as $type) {
            $key = 'meters.pvs.'.$type;
            $fields = array_merge($fields, array_keys(config($key)));
        }

        return $fields;
    }

    public function getHelper()
    {
        switch ($this->type) {
            case 'power':
                return new PowerMeterHelper($this);
            case 'water':
                return new WaterMeterHelper($this);
            case 'gas':
                return new MeterHelper($this);
        }

        return null;
    }

    public function getPresenter()
    {
        switch ($this->type) {
            case 'power':
                return new PowerMeterPresenter($this);
            case 'water':
                return new WaterMeterPresenter($this);
            case 'gas':
                return new GasMeterPresenter($this);
        }

        return null;
    }

    public function hasRolloverIncrement($field)
    {
        if ($this->rolloverIncrement($field) !== null) {
            return true;
        }

        return false;
    }

    public function hasRolloverEvents()
    {
        return $this->rolloverEvents->isNotEmpty();
    }

    public function rolloverIncrement($field)
    {
        $key = 'meters.rollover.'.$this->model_number.'.'.$field;
        $increment = config($key, null);

        return $increment;
    }

    /**
     * Returns the most current accumulated rollover for the meter or zero
     * if there is none.
     */
    public function accumulatedRollover(string $field): int
    {
        if ($this->lastRolloverEvent($field)) {
            return $this->lastRolloverEvent($field)->rollover_accumulated;
        }

        return 0;
    }

    /**
     * Returns the names of the fields for which rollover
     * limits are defined.
     */
    public function rolloverFields()
    {
        $key = 'meters.rollover.'.$this->model_number;
        $fields = config($key, null);
        if ($fields) {
            return array_keys($fields);
        }

        return [];
    }

    /**
     * @return null
     *
     * @throws MeterDataException
     */
    public function consumedBetween($field, Carbon $fromDate, Carbon $toDate)
    {
        $query = $this->dataTable()
            ->select(['date', $field])
            ->where('meter_id', $this->id)
            ->where('date', '>=', $fromDate)
            ->where('date', '<=', $toDate)
            ->whereNotNull($field)
            ->orderBy('date', 'asc');
        $data = $query->get();
        $firstVal = $data->first() ? $data->first()->$field : null;
        $lastVal = $data->last() ? $data->last()->$field : null;

        if (! $firstVal === null || $lastVal === null) {
            $message = sprintf('Encountered null data in the %s column', $field);
            throw new MeterDataException($message, $this);
        }

        if ($firstVal == 0 && $this->begins_at && $fromDate->greaterThan($this->begins_at)) {
            $message = sprintf('Encountered unexpected 0 in the %s column at %s', $field, $fromDate);
            throw new MeterDataException($message, $this);
        }

        if ($firstVal > $lastVal) {
            $message = sprintf('Value of %s decreased from %s between %s', $field, $fromDate, $toDate);
            throw new MeterDataException($message, $this);
        }

        return $lastVal - $firstVal;
    }

    /**
     * Return the timestamp and value of the first data value available
     * on or after the specified date.
     *
     * @return \Illuminate\Database\Eloquent\Model|Builder|object
     *
     * @throws \Exception
     */
    public function firstDataOnOrBefore($field, Carbon $atDate)
    {
        $query = $this->lastDataQuery($field)
            ->where('date', '<=', $atDate);
        $datum = $query->first();
        if (isset($datum->date)) {
            $datum->date = Carbon::createFromFormat('Y-m-d H:i:s', $datum->date);
        }

        return $datum;
    }

    /**
     * Get the earliest available non-null data for the meter.
     * Optionally restrict the context to a specific PV field.
     *
     * @param  null  $field
     *
     * @throws \Exception
     */
    public function firstDataQuery($field = null): Builder
    {
        // base query
        $query = $this->baseFirstOrLastQuery(false);
        // include a field in the select or just the date?
        if ($field) {
            $query->addSelect($field)->whereNotNull($field);
        }

        // Return the prepared query object
        return $query;
    }

    /**
     * Get the earliest available non-null data for the meter.
     * Optionally restrict the context to a specific PV field.
     *
     * @param  null  $field
     *
     * @throws \Exception
     */
    public function lastDataQuery($field = null): Builder
    {
        $query = $this->baseFirstOrLastQuery(true);
        if ($field) {
            $query->addSelect($field);
        }

        return $query;
    }

    /**
     * Returns a query to select the first or last date
     *
     *
     * @throws \Exception
     */
    protected function baseFirstOrLastQuery($last = false): Builder
    {
        $query = $this->dataTable()
            ->select('date')
            ->where('meter_id', $this->id)
            ->limit(1);
        if ($last) {
            $query->orderBy('date', 'desc');
        } else {
            $query->orderBy('date');
        }

        return $query;
    }

    /**
     * Return the timestamp and value of the first data value available
     * on or after the specified date.
     *
     *
     * @throws \Exception
     */
    public function firstDataOnOrAfter($field, Carbon $atDate)
    {
        $query = $this->firstDataQuery($field)
            ->where('date', '>=', $atDate);
        $datum = $query->first();
        if (isset($datum->date)) {
            $datum->date = Carbon::createFromFormat('Y-m-d H:i:s', $datum->date);
        }

        return $datum;
    }

    /**
     * Return the timestamp and value of the first data value available
     * between two dates.
     *
     * @param  Carbon  $atDate
     *
     * @throws \Exception
     */
    public function firstDataBetween($field, Carbon $beginDate, Carbon $endDate)
    {
        $query = $this->firstDataQuery($field)
            ->whereBetween('date', [$beginDate, $endDate]);
        $datum = $query->first();
        if (isset($datum->date)) {
            $datum->date = Carbon::createFromFormat('Y-m-d H:i:s', $datum->date);
        }

        return $datum;
    }

    /**
     * Return the timestamp and value of the first data value available
     * between two dates.
     *
     * @param  Carbon  $atDate
     *
     * @throws \Exception
     */
    public function lastDataBetween($field, Carbon $beginDate, Carbon $endDate)
    {
        $query = $this->lastDataQuery($field)
            ->whereBetween('date', [$beginDate, $endDate]);
        $datum = $query->first();
        if (isset($datum->date)) {
            $datum->date = Carbon::createFromFormat('Y-m-d H:i:s', $datum->date);
        }

        return $datum;
    }

    /**
     * Returns statistics for the given interval
     *
     * @return mixed object(avg, stddev, min, max) | null
     *
     * @throws \Exception
     */
    public function statsBetween($field, Carbon $fromDate, Carbon $toDate)
    {
        if (config('database.default') == 'mysql') {
            $query = $this->dataTable()
                ->select(DB::raw("AVG($field) as avg,  MIN($field) as min, MAX($field) as max, STDDEV($field) as stddev"));
        } else {
            // sqlite used for testing doesn't have thes STDEV function like mysql does above.
            $query = $this->dataTable()
                ->select(DB::raw("AVG($field) as avg,  MIN($field) as min, MAX($field) as max"));
        }
        $query->where('meter_id', $this->id)
            ->where('date', '>=', $fromDate)
            ->where('date', '<=', $toDate)
            ->whereNotNull($field);

        return $query->first();
    }

    public function reporter(): DataTableReporter
    {
        if (! $this->reporter) {
            $this->reporter = new DataTableReporter($this);
        }

        return $this->reporter;
    }


    /**
     * Update fields for existing meter data rows.
     *
     *
     * @throws \Exception
     */
    public function updateDataTable(Carbon $begin, array $fields = []): int
    {
        // Default to updating all channels
        if (empty($fields)) {
            $channels = $this->channels();
        } else {
            $channels = $this->makeChannels($fields);
        }

        $updated = 0;
        try {
            $mySampler = new MySamplerData($begin, $channels);
            $items = $mySampler->getData();
            foreach ($items as $item) {
                try {
                    $result = $this->dataTable()
                        ->where('date', $item['date'])
                        ->where('meter_id', $this->id)
                        ->update($this->columnsFromMySampler($item));
                    $updated += $result;
                } catch (\PDOException $e) {
                    Log::error($e);
                }
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error($e->getMessage());
        }

        return $updated;
    }

    /**
     * Turns an array of field names into an array of PV channel names
     * by prepending the meter's epics name.
     */
    protected function makeChannels(array $fields): array
    {
        $channels = [];
        foreach ($fields as $field) {
            if (substr($field, 0, 1) != ':') {
                $field = ':'.$field;
            }
            $channels[] = $this->epics_name.$field;
        }

        return $channels;
    }

    /**
     * The list of fully qualified PV channel names for the current meter.
     */
    public function channels(): array
    {
        return $this->makeChannels($this->pvFields());
    }

    public function dbFields(): array
    {
        return $this->pvFields();
    }

    /**
     * Returns the array of fields that can be appended to
     * epics_name to form pvs.
     */
    public function pvFields(): array
    {
        $key = 'meters.pvs.'.$this->type;

        return array_keys(config($key));
    }

    /**
     * The name of the table where meter data points are stored.
     */
    public function tableName(): string
    {
        return $this->type.'_meter_data_'.$this->id;
    }

    /**
     * Convert the data returned from MySamplerData into an array
     * suitable for use with a DB::insert().
     *
     * @param  $item  - element of array returned by MySampler
     */
    protected function columnsFromMySampler($item): array
    {
        $columns = ['meter_id' => $this->id];
        foreach ($item as $key => $value) {
            if ($key == 'date') {
                $columns['date'] = $value;
            } else {
                $name = str_replace($this->epics_name, '', $key);
                $name = substr($name, 1);  // strip leading colon character
                $columns[$name] = $value;
            }
        }

        return $columns;
    }
}
