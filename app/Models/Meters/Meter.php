<?php

namespace App\Models\Meters;

use App\Exceptions\MeterDataException;
use App\Exceptions\ModelValidationException;
use App\Models\BaseModel;
use App\Models\DataTables\DataTableInterface;
use App\Models\DataTables\DataTableReporter;
use App\Models\DataTables\DataTableTrait;
use App\Presenters\PowerMeterPresenter;
use App\Presenters\WaterMeterPresenter;
use App\Presenters\GasMeterPresenter;
use App\Utilities\MySamplerData;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Robbo\Presenter\PresentableInterface;

class Meter extends BaseModel implements PresentableInterface, DataTableInterface
{

    use DataTableTrait;

    protected $table = 'meters';

    public static $rules = array(
        'name' => 'required | max:80',
        'type' => 'required | in:power,water,gas',
        'epics_name' => 'max:40',
        'name_alias' => 'max:80',
    );
    public $fillable = array(
        'name',
        'type',
        'building_id',
        'epics_name',
        'name_alias',
        'model_number',
        'housed_by',
        'begins_at'
    );
    protected $reporter;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'begins_at'];


    /**
     * Meter constructor.
     *
     */
    public function __construct(array $attributes = array())
    {
        $this->dataTableFk = 'meter_id';
        parent::__construct($attributes);
    }

    public static function typeFromCEDType($cedType)
    {
        switch (strtolower($cedType)) {
            case 'powermeter' :
                return 'power';
            case 'watermeter' :
                return 'water';
            case 'gasmeter' :
                return 'gas';
        }
        return null;
    }

    public function findByPv($pv)
    {
        return Meter::fromPv($pv)->first();  // epics_name must be unique
    }

    public function scopeFromPv($query, $pv){
        return $query->where('epics_name', '=', self::epicsNameFromPv($pv));
    }


    public function meterLimits()
    {
        return $this->hasMany(MeterLimit::class);
    }

    public function rolloverEvents()
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
            return !$this->meterLimits->where('field', $field)->isEmpty();
        }
        return !$this->meterLimits->isEmpty();
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
                        $event = new RolloverEvent();
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


    /**
     * @param string $field
     * @param float $accumulatedRollover
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @return mixed
     * @TODO recalculate the totMBTU column too (update power_meter_data set totMBTU = totkWh * 0.00341214)
     * @throws \Exception
     */
    protected function applyRollover($field, $accumulatedRollover, Carbon $fromDate, Carbon $toDate){
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
     * Updates the data table so that perpetually incrementing fields do in fact
     * increment perpetually by removing the effect of rollover events that have happened.
     *
     */
    public function applyRolloverEvents()
    {
        $rowsUpdated = 0;
        for ($i=0; $i<$this->rolloverEvents->count(); $i++){
            $rolloverToApply = $this->rolloverEvents->slice($i,1)->first();
            if ($i < $this->rolloverEvents->count() -1){
                $stopAt = $this->rolloverEvents->slice($i+1,1)->first()->rollover_at;
            }else{
                $stopAt = Carbon::now();
            }
            $rowsUpdated += $this->applyRollover($rolloverToApply->field, $rolloverToApply->rollover_accumulated, $rolloverToApply->rollover_at, $stopAt);
        }
        return $rowsUpdated;
    }



    /**
     * Answers whether the provided value is within the limits for the
     * specified field.
     * @param string $field
     * @param mixed $value numeric value
     * @return boolean
     */
    public function withinLimits($field, $value)
    {
        if (!$this->hasMeterLimits($field)) {
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
     * @param $pv
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
     * @return array
     */
    public static function allPvFields()
    {
        $fields = array();
        $types = array_keys(config('meters.pvs'));
        foreach ($types as $type) {
            $key = 'meters.pvs.' . $type;
            $fields = array_merge($fields, array_keys(config($key)));
        }
        return $fields;
    }


    public function getHelper()
    {
        switch ($this->type) {
            case 'power' :
                return new PowerMeterHelper($this);
            case 'water' :
                return new WaterMeterHelper($this);
            case 'gas' :
                return new MeterHelper($this);
        }
        return null;
    }


    public function getPresenter()
    {
        switch ($this->type) {
            case 'power' :
                return new PowerMeterPresenter($this);
            case 'water' :
                return new WaterMeterPresenter($this);
            case 'gas' :
                return new GasMeterPresenter($this);
        }
        return null;
    }


    function hasRolloverIncrement($field)
    {
        if ($this->rolloverIncrement($field) !== null) {
            return true;
        }
        return false;
    }

    function rolloverIncrement($field)
    {
        $key = 'meters.rollover.' . $this->model_number . '.' . $field;
        $increment = config($key, null);
        return $increment;
    }

    /**
     * Returns the most current accumulated rollover for the meter or zero
     * if there is none.
     *
     * @param string $field
     * @return integer
     */
    function accumulatedRollover($field){
        if ($this->lastRolloverEvent($field)){
            return $this->lastRolloverEvent($field)->rollover_accumulated;
        }
        return 0;
    }

    /**
     * Returns the names of the fields for which rollover
     * limits are defined.
     */
    function rolloverFields()
    {
        $key = 'meters.rollover.' . $this->model_number;
        $fields = config($key, null);
        if ($fields) {
            return array_keys($fields);
        }
        return array();
    }


    /**
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @param $field
     * @return null
     * @throws MeterDataException
     */
    function consumedBetween($field, Carbon $fromDate, Carbon $toDate)
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

        if (!$firstVal === null || $lastVal === null) {
            $message = sprintf("Encountered null data in the %s column", $field);
            throw new MeterDataException($message, $this);
        }

        if ($firstVal == 0 && $fromDate->greaterThan($this->begins_at)) {
            $message = sprintf("Encountered unexpected 0 in the %s column at %s", $field, $fromDate);
            throw new MeterDataException($message, $this);
        }

        if ($firstVal > $lastVal) {
            $message = sprintf("Value of %s decreased from %s between %s", $field, $fromDate, $toDate);
            throw new MeterDataException($message, $this);
        }

        return $lastVal - $firstVal;

    }


    /**
     * Return the timestamp and value of the first data value available
     * on or after the specified date.
     * @param $field
     * @param Carbon $atDate
     * @return \Illuminate\Database\Eloquent\Model|Builder|object
     * @throws \Exception
     */
    function firstDataOnOrBefore($field, Carbon $atDate)
    {
        $query = $this->dataTable()
            ->select(['date', $field])
            ->where('meter_id', $this->id)
            ->where('date', '<=', $atDate)
            ->whereNotNull($field)
            ->orderBy('date', 'desc')
            ->limit(1);
        $datum = $query->first();
        //dd($query->toSql());
        if (isset($datum->date)) {
            $datum->date = Carbon::createFromFormat('Y-m-d H:i:s', $datum->date);
        }
        return $datum;
    }


    /**
     * Return the timestamp and value of the first data value available
     * on or after the specified date.
     * @param $field
     * @param Carbon $atDate
     * @throws \Exception
     */
    function firstDataOnOrAfter($field, Carbon $atDate)
    {
        $query = $this->dataTable()
            ->select(['date', $field])
            ->where('meter_id', $this->id)
            ->where('date', '>=', $atDate)
            ->whereNotNull($field)
            ->orderBy('date', 'asc')
            ->limit(1);
        $datum = $query->first();
        //dd($query->toSql());
        if (isset($datum->date)) {
            $datum->date = Carbon::createFromFormat('Y-m-d H:i:s', $datum->date);
        }
        return $datum;
    }

    /**
     * Returns statistics for the given interval
     * @param $field
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @return mixed object(avg, stddev, min, max) | null
     * @throws \Exception
     */
    function statsBetween($field, Carbon $fromDate, Carbon $toDate)
    {
        // Not compatible with sqllite used for testing:
        //      STDDEV($field) as stddev
        // If we actually need it, perhaps
        // @see https://stackoverflow.com/questions/2298339/standard-deviation-for-sqlite/24423341

        $query = $this->dataTable()
            ->select(DB::raw("AVG($field) as avg,  MIN($field) as min, MAX($field) as max"))
            ->where('meter_id', $this->id)
            ->where('date', '>=', $fromDate)
            ->where('date', '<=', $toDate)
            ->whereNotNull($field);
        return $query->first();
    }


    /**
     * @return DataTableReporter
     */
    public function reporter()
    {
        if (!$this->reporter) {
            $this->reporter = new DataTableReporter($this);
        }
        return $this->reporter;
    }


    /**
     * Insert new meter data rows.
     *
     * @return int|mixed
     * @throws \Exception
     */
    public function fillDataTable()
    {
        $inserted = 0;
        try {
            $mySampler = new MySamplerData($this->nextDataDate(), $this->channels());
            $items = $mySampler->getData();

            foreach ($items as $item) {
                try {
                    $this->dataTable()->insert($this->columnsFromMySampler($item));
                    $inserted++;
                } catch (\PDOException $e) {
                    Log::error($e);
                }
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error($e->getMessage());
        }
        return $inserted;
    }

    /**
     * Update fields for existing meter data rows.
     *
     * @param Carbon $begin
     * @param array $fields
     * @return int
     * @throws \Exception
     */
    public function updateDataTable(Carbon $begin, $fields=[])
    {
        // Default to updating all channels
        if (empty($fields)){
            $channels = $this->channels();
        }else{
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
     * Turns an array of field namese into an array of PV channel names
     * by prepending the meter's epics name.
     *
     * @param array $fields
     * @return array
     */
    protected function makeChannels(array $fields){
        $channels = [];
        foreach ($fields as $field) {
            if (substr($field, 0, 1) != ':'){
                $field = ':'.$field;
            }
            $channels[] = $this->epics_name . $field;
        }
        return $channels;
    }

    /**
     * Returns the full list of PV channel names for the current meter.
     *
     * @return array
     */
    public function channels()
    {
        return $this->makeChannels($this->pvFields());
    }

    /**
     * Returns the array of fields that can be appended to
     * epics_name to form pvs.
     * @return array
     */
    public function pvFields()
    {
        $key = 'meters.pvs.' . $this->type;
        return array_keys(config($key));
    }

    /**
     * Returns a Query Builder for the appropriate data table.
     *
     * @return Builder
     * @throws \Exception if physical meters are of different types;
     */
    public function dataTable()
    {
        //$name = $this->type . '_meter_daily_consumption';
        $name = $this->type . '_meter_data';
        return DB::table($name);
    }

    /**
     * Convert the data returned from MySamplerData into an array
     * suitable for use with a DB::insert().
     *
     * @param $item - element of array returned by MySampler
     * @return array
     */
    protected function columnsFromMySampler($item)
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
