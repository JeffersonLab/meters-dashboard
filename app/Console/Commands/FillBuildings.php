<?php

namespace App\Console\Commands;

use App\Models\Buildings\Building;
use App\Models\DataTables\DataTableModifier;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use App\Exceptions\WebClientException;


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
     */
    public function handle(): int
    {
        ini_set('memory_limit', '1G');
        $existing = Building::all();
        foreach ($existing as $building) {
            try {
                if ($building->type != 'CoolingTower'){ 
                    $this->info('Try '.$building->name);               
                    $count = $building->fillDataTable();
                    $this->info('Filled '.$building->name." with $count rows");
                }
            } catch (QueryException $e) {
                if (stristr($e->getMessage(), " Unknown column 'ccf'")) {
                    $this->error('Must add gas meter columns to '.$building->id);
                    // Fix the problem in anticipation of future runs.
                    $this->addGasMeterColumns($building);
                }
                if (stristr($e->getMessage(), " Unknown column 'gal'")) {
                    $this->error('Must add water meter columns to '.$building->id);
                    // Fix the problem in anticipation of future runs.
                    $this->addWaterMeterColumns($building);
                }
            } catch (WebClientExeption $e) {
                $this->error($e->getMessage());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Add gas meter columns to the building.
     *
     *
     * @throws \App\Exceptions\DataConversionException
     */
    protected function addGasMeterColumns(Building $building)
    {
        $modifier = new DataTableModifier($building);
        $modifier->addGasMeterColumns();
    }
    
    /**
     * Add water meter columns to the building.
     *
     *
     * @throws \App\Exceptions\DataConversionException
     */
    protected function addWaterMeterColumns(Building $building)
    {
        $modifier = new DataTableModifier($building);
        $modifier->addWaterMeterColumns();
    }
}
