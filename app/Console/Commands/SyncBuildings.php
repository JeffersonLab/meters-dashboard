<?php

namespace App\Console\Commands;

use App\Models\Buildings\Building;
use App\Utilities\CEDElemData;
use App\Utilities\CEDTypeData;
use Illuminate\Console\Command;

class SyncBuildings extends Command
{
    use SyncTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildings:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Buildings table with CED';

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
     */
    public function __construct(CEDTypeData $cedTypeData, CEDElemData $cedElemData)
    {
        parent::__construct();
        $this->cedTypeData = $cedTypeData;
        $this->cedElemData = $cedElemData;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->cedTypeData->type = 'Structure';
            $data = $this->cedTypeData->getData();
            $this->update($data);
            // @TODO deletes
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }

    /**
     * Uses retrieved data to update attributes of buildings that already exist in the database.
     *
     * Because we only want the local database to contain buildings that have meters assigned to them,
     * the responsibility for initialy adding a meter belongs to the SyncMeters command.  The current
     * command's purpose is simply to update existing meter attributes.
     *
     *
     * @throws \Throwable
     */
    protected function update($data)
    {
        $existing = Building::all();
        foreach ($existing as $building) {
            $match = $data->where('name', $building->name)->first();
            if ($match) {
                $updated = $building->update([
                    'element_id' => $match->id,
                    'type' => $match->type,
                    'is_metered' => $this->propertyFromItem($match, 'isMetered'),
                    'abbreviation' => $this->propertyFromItem($match, 'Abbreviation'),
                    'address' => $this->propertyFromItem($match, 'Address'),
                    'building_num' => $this->propertyFromItem($match, 'BuildingNum'),
                    'square_footage' => $this->propertyFromItem($match, 'SquareFootage'),
                ]);
                if ($updated) {
                    $this->line("Updated $building->name");
                } else {
                    $this->error(("Error attempting to update $building->name"));
                }
            } else {
                $this->error(("No match for $building->name"));
            }
        }
    }
}
