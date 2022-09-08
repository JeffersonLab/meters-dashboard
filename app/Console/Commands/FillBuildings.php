<?php

namespace App\Console\Commands;

use App\Models\Buildings\Building;
use App\Models\DataTables\DataTableModifier;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

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
            } catch (QueryException $e){
                if (strstr($e->getMessage(), " Unknown column 'ccf'")){
                    $this->error('Must add gas meter columns to '.$building->id);
                    // Fix the problem in anticipation of future runs.
                    $this->addGasMeterColumns($building);
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                throw $e;
            }
        }
        return true;
    }

    /**
     * Add gas meter columns to the building.
     * @param Building $building
     * @throws \App\Exceptions\DataConversionException
     */
    protected function addGasMeterColumns($building){
        $modifier = new DataTableModifier($building);
        $modifier->addGasMeterColumns();
    }


}
