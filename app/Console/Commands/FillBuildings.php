<?php

namespace App\Console\Commands;

use App\Meters\Building;
use Illuminate\Console\Command;

class FillBuildings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildings:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills building data table by sampling mya archiver';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $existing = Building::all();
        foreach ($existing as $building) {
            try {
                $count = $building->fillDataTable();
                $this->info('Filled ' . $building->name . "with $count rows");
            } catch (\Exception $e) {
                //$this->error($e->getMessage());
                throw $e;
            }
        }
        return true;
    }


}
