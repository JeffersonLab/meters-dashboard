<?php

namespace App\Console\Commands;

use App\Exceptions\ModelValidationException;
use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use App\Utilities\CEDElemData;
use App\Utilities\CEDTypeData;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncMeters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meters:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Meters table with CED';


    /**
     * The CED Type Data Helper.
     *
     * @var CEDTypeData
     */
    protected $cedTypeData;

    /**
     * The CED Elem Data Helper.
     *
     * @var CEDElemData
     */
    protected $cedElemData;


    /**
     * Create a new command instance.
     *
     * @param CEDTypeData $cedTypeData
     * @param CEDElemData $cedElemData
     */
    public function __construct(CEDTypeData $cedTypeData, CEDElemData $cedElemData)
    {
        parent::__construct();
        $this->cedTypeData = $cedTypeData;
        $this->cedElemData = $cedElemData;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->cedTypeData->type = 'Meter';
            $data = $this->cedTypeData->getData();
            $this->addAndUpdate($data);
            // @TODO deletes
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Uses retrieved data to create Meters that don't yet exist in the database
     * and to update
     *
     * @param $data
     * @throws \Throwable
     */
    protected function addAndUpdate($data)
    {
        $existing = Meter::all();

        foreach ($data as $item) {
            if ($this->propertyFromItem($item, 'Unpowered') != 1) {

                if (!$existing->contains('name', $item->name)) {

                    $meter = new Meter([
                        'name' => $item->name,
                        'type' => Meter::typeFromCEDType($item->type),
                        'epics_name' => $this->propertyFromItem($item, 'EPICSName'),
                        'name_alias' => $this->propertyFromItem($item, 'NameAlias'),
                        'model_number' => $this->propertyFromItem($item, 'ModelNumber'),
                        'housed_by' => $this->propertyFromItem($item, 'Housed_by'),
                        'begins_at' => Carbon::now(),

                    ]);
                    $meter->saveOrFail();
                    $this->info('Added ' . $item->name);

                } else {
                    $meter = $existing->where('name', '=', $item->name)->first();
                    if ($this->differs($meter, $item)) {
                        $meter->fill([
                            'epics_name' => $this->propertyFromItem($item, 'EPICSName'),
                            'name_alias' => $this->propertyFromItem($item, 'NameAlias'),
                            'model_number' => $this->propertyFromItem($item, 'ModelNumber'),
                            'housed_by' => $this->propertyFromItem($item, 'Housed_by'),
                        ]);
                        $meter->saveOrFail();
                        $this->info('Updated ' . $item->name);
                    }

                }
                $this->syncBuilding($meter);
            }
        }
    }

    /**
     * Extract a property value from data structure if it exists.  Null otherwise.
     *
     * @param object $item
     * @param string $property
     * @return mixed
     */
    protected function propertyFromItem($item, $property)
    {
        if (isset($item->properties) && isset($item->properties->$property)) {
            return $item->properties->$property;
        }
        return null;
    }

    /**
     * Determine whether data item content differs from existing meter object
     */
    protected function differs($meter, $item)
    {
        $epics_name = isset($meter->epics_name) ? $meter->epics_name : null;
        if ($epics_name != $this->propertyFromItem($item, 'EPICSName')) {
            return true;
        }

        $name_alias = isset($meter->name_alias) ? $meter->name_alias : null;
        if ($name_alias != $this->propertyFromItem($item, 'NameAlias')) {
            return true;
        }

        $model_number = isset($meter->model_number) ? $meter->model_number : null;
        if ($model_number != $this->propertyFromItem($item, 'ModelNumber')) {
            return true;
        }

        $housed_by = isset($meter->housed_by) ? $meter->housed_by : null;
        if ($housed_by != $this->propertyFromItem($item, 'Housed_by')) {
            return true;
        }

        return false;
    }

    /**
     * Ensures that the building housing the meter is also in the local database.
     *
     * @param Meter $meter
     */
    public function syncBuilding(Meter $meter)
    {
        if (!$meter->building_id) {
            if ($meter->housed_by) {
                $building = $this->getBuilding($meter->housed_by);

                if ($building) {
                    $meter->building_id = $building->id;
                    try {
                        $meter->saveOrFail();
                    } catch (ModelValidationException $e) {
                        $this->error($building->name . ': ' . $e->getMessage());
                    }
                    $this->line('Assigned building id' . $building->id . ' to ' . $meter->name);
                }
            }
        }
    }

    /**
     * Obtain building object by its name.
     * Goes to CED if not found in local DB
     * @param $name
     * @return Building|mixed|null
     */
    protected function getBuilding($name)
    {
        $localBuilding = Building::where('name', '=', $name)->first();
        if (!$localBuilding) {
            return $this->getBuildingFromCed($name);
        }
        return $localBuilding;
    }

    /**
     * Acquires building data from CED and saves it as a local Building object
     * which is then returned.
     *
     * @param $name
     * @return Building|null
     */
    protected function getBuildingFromCed($name)
    {
        $this->cedElemData->elem = $name;
        $item = $this->cedElemData->getData();
        if ($item) {
            $building = new Building([
                'name' => $item->name,
                'abbreviation' => $this->propertyFromItem($item, 'Abbreviation'),
                'building_num' => $this->propertyFromItem($item, 'BuildingNum'),
                'jlab_name' => $this->propertyFromItem($item, 'JLabName'),
                'square_footage' => $this->propertyFromItem($item, 'SquareFootage'),
            ]);
            try {
                $building->saveOrFail();
            } catch (ModelValidationException $e) {
                $this->error($building->name . ': ' . $e->getMessage());
            }
            $this->line('Added building ' . $building->name . 'from CED');
            return $building;
        }
        return null;
    }

}
