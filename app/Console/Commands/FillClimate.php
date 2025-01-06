<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillClimate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'climate:fill
                {--date= : Yesterday is assumed unless specified (yyyy-mm-dd) }
                {--source=jlab : specify data source (darksky | jlab) }
                {--replace : Replace existing data }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills climate data table from historical weather data source';

    protected function getDataSource()
    {
        if ($this->hasOption('source') && $this->option('source') == 'darksky') {
            $dataSource = new \App\Utilities\DarkSkyClimateData;
        } else {
            $dataSource = new \App\Utilities\FacilitiesClimateData;
        }

        if ($this->hasOption('date')) {
            $dataSource->setDate($this->option('date'));
        }

        return $dataSource;
    }

    /**
     * Execute the console command.
     *
     *
     * @throws \Exception
     */
    public function handle(): int
    {
        try {
            $data = $this->getDataSource();

            if ($this->hasOption('replace')) {
                DB::table('climate_data')->where('date', $data->getDate())->delete();
            }

            DB::table('climate_data')->insert([
                'date' => $data->getDate(),
                'heating_degree_days' => $data->heatingDegreeDays(),
                'cooling_degree_days' => $data->coolingDegreeDays(),
                'degree_days' => $data->heatingDegreeDays() + $data->coolingDegreeDays(),
                'src' => $data->sourceName(),
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            throw $e;
        }

        return true;
    }
}
