<?php

namespace App\Models\Buildings;

use App\Exceptions\ReportingException;
use App\Models\BaseModel;
use App\Models\DataTables\BuildingDataTableReporter;
use App\Models\DataTables\DataTableCreator;
use App\Models\DataTables\DataTableInterface;
use App\Models\DataTables\DataTableReporter;
use App\Models\DataTables\DataTableTrait;
use App\Models\Meters\Meter;
use App\Presenters\BuildingPresenter;
use App\Utilities\MySampler;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Robbo\Presenter\PresentableInterface;

class Building extends BaseModel implements DataTableInterface, PresentableInterface
{
    use DataTableTrait;
    use SoftDeletes;

    protected $reporter;

    protected $nonBuildingFields = [':llVolt'];

    public static $rules = [
        'name' => 'required | max:80',
        'abbreviation' => 'max:20',
        'building_num' => 'max:20',
        'jlab_name' => 'max:80',
        'square_footage' => 'nullable | numeric | min:0',

    ];

    public $fillable = ['name', 'element_id', 'type', 'abbreviation', 'building_num', 'square_footage', 'jlab_name'];

    /**
     * Building constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->dataTableFk = 'building_id';
        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'begins_at' => 'datetime',
        ];
    }

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class);
    }

    /**
     * Returns a list of the types of meters (power, water, gas)
     * the building contains.
     */
    public function meterTypes()
    {
        return $this->meters()->pluck('type')->unique()->all();
    }

    public function hasMeterType($type)
    {
        return in_array($type, $this->meterTypes());
    }

    public function metersOfType($type)
    {
        return $this->meters()->where('type', '=', $type);
    }

    public function gasMeters()
    {
        return $this->metersOfType('gas');
    }

    public function powerMeters()
    {
        return $this->metersOfType('power');
    }

    public function waterMeters()
    {
        return $this->metersOfType('water');
    }

    public function waterSupplyMeters()
    {
        return $this->waterMeters()->where('epics_name', 'LIKE', '%SUPPLY%');
    }

    public function waterDrainMeters()
    {
        return $this->waterMeters()->where('epics_name', 'LIKE', '%DRAIN%');
    }

    /**
     * The sum of the gallons through the building's water supply meters.
     */
    public function waterConsumption(Carbon $fromDate, Carbon $toDate): float
    {
        $consumed = 0.0;
        foreach ($this->waterSupplyMeters()->get() as $meter) {
            $consumed += $meter->consumedBetween('gal', $fromDate, $toDate);
        }

        return $consumed;
    }

    /**
     * The sum of the gallons through the building's water drain meters.
     */
    public function waterToSewer(Carbon $fromDate, Carbon $toDate): float
    {
        $consumed = 0.0;
        foreach ($this->waterDrainMeters()->get() as $meter) {
            $consumed += $meter->consumedBetween('gal', $fromDate, $toDate);
        }

        return $consumed;
    }

    /**
     * The sum of the gallons through the building's water drain meters.
     */
    public function waterToEvaporation(Carbon $fromDate, Carbon $toDate): float
    {
        return $this->waterConsumption($fromDate, $toDate) - $this->waterToSewer($fromDate, $toDate);
    }

    /**
     * The sum of the gallons through the building's water drain meters.
     *
     *
     * @throws ReportingException
     */
    public function waterCyclesOfConcentration(Carbon $fromDate, Carbon $toDate): float
    {
        $toSewer = $this->waterToSewer($fromDate, $toDate);
        if ($toSewer != 0) {
            return $this->waterConsumption($fromDate, $toDate) / $toSewer;
        }
        throw new ReportingException('Divide by 0 error computing cycles of concentration '.$this->id);
    }

    public function getPresenter()
    {
        return new BuildingPresenter($this);
    }

    public function reporter(): DataTableReporter
    {
        if (! $this->reporter) {
            $this->reporter = new BuildingDataTableReporter($this);
        }

        return $this->reporter;
    }

    /**
     * The name of the table where meter data points are stored.
     */
    public function tableName(): string
    {
        return 'building_data_'.$this->id;
    }

    public function fillDataTable()
    {
        $inserted = 0;
        if (! empty($this->channels())){
            try {
                // We ask the mya server for data no more than 1000 items at a time
                // until we are all caught up.
                while (strtotime($this->nextDataDate()) < time()) {
                    $mySampler = new MySampler($this->nextDataDate(), $this->channels());
                    $items = $mySampler->getData();
                    if ($items->isEmpty()) {
                        break;  // must escape the while loop when no more data
                    }
                    foreach ($items as $item) {
                        try {
                            $this->dataTable()->insert($this->columnsFromMySampler($item));
                            $inserted++;
                        } catch (\PDOException $e) {
                            Log::error($e);
                            throw $e;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                throw $e;
            }
        }else{
            Log::warning("{$this->getPresenter()->menuLabel()} has no channels to fetch.");
        }

        return $inserted;
    }
//    public function fillDataTable()
//    {
//        $inserted = 0;
//        try {
//            // We ask the mya server for data no more than 1000 items at a time
//            // until we are all caught up.
//            while (strtotime($this->nextDataDate()) < time()) {
//                $mySampler = new MySamplerData($this->nextDataDate(), $this->channels());
//                $items = $mySampler->getData();
//                if ($items->isEmpty()) {
//                    break;  // must escape the while loop when no more data
//                }
//                foreach ($items as $item) {
//                    try {
//                        $this->dataTable()->insert($this->columnsFromMySampler($item));
//                        $inserted++;
//                    } catch (\PDOException $e) {
//                        Log::error($e);
//                        throw $e;
//                    }
//                }
//            }
//        } catch (\GuzzleHttp\Exception\ClientException $e) {
//            Log::error($e->getMessage());
//            //throw ($e);
//        }
//
//        //var_dump('inserted '.$inserted);
//        return $inserted;
//    }

    public function channels()
    {
        $channels = [];
        foreach ($this->pvFields() as $field) {
            // buildings only have name, not epics_name
            $channels[] = $this->name.$field;
        }

        return $channels;
    }

    /**
     * Convert the data returned from MySamplerData into an array
     * suitable for use with a DB::insert().
     *
     * @param  $item  - element of array returned by MySampler
     */
    protected function columnsFromMySampler($item): array
    {
        $columns = ['building_id' => $this->id];
        foreach ($item as $key => $value) {
            if ($key == 'date') {
                $columns['date'] = $value;
            } else {
                // buildings only have name, not epics_name
                $name = str_replace($this->name, '', $key);
                $name = substr($name, 1);  // strip leading colon character
                $columns[$name] = $value;
            }
        }

        return $columns;
    }

    /**
     * Removes from an array of field names those which do not apply to buildings.
     *
     * The default assumption that a building has all the
     * pv fields of the meter types it houses may not be entirely
     * valid.  For example buildings with power meters do not
     * have an llVolt field.
     */
    protected function removeNonBuildingFields(array $fields)
    {
        // strip out llVolt which doesn't apply to buildings
        return array_diff($fields, $this->nonBuildingFields);
    }

    /**
     * Returns the array of fields that can be appended to
     * epics_name to form pvs.
     *
     * Buildings can have a mix of power, water, and gas readings.
     * This function will return the master list of possible fields.
     * Use pvFields() to limit the list based on the type of meters
     * actively associated with the building.
     */
    public function dbFields(): array
    {
        //They are total for the building and could be from a single
        //meter or by summing multiple.  Gary takes care of this
        //at the IOC level and simply provides a single building PV
        //for each.
        $fields = [];
        foreach (array_keys(config('meters.pvs')) as $type) {
            $fields = array_merge($fields, array_keys(config('meters.pvs.'.$type)));
        }

        return $this->removeNonBuildingFields($fields);
    }

    /**
     * Returns the array of fields that can be stored in the database
     *
     * Buildings can have a mix of power, water, and gas readings.
     * This function will only return a list of fields relevant to
     * the types of meters associated with the building.
     */
    public function pvFields(): array
    {
        //They are total for the building and could be from a single
        //meter or by summing multiple.  Gary takes care of this
        //at the IOC level and simply provides a single building PV
        //for each.
        $fields = [];
        foreach ($this->meterTypes() as $type) {
            $fields = array_merge($fields, array_keys(config('meters.pvs.'.$type)));
        }

        return $this->removeNonBuildingFields($fields);
    }

    public function save(array $options = [])
    {
        $saved = parent::save($options);
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
}
