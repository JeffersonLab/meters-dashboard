<?php

namespace App\Console\Commands;

use App\Models\Meters\Meter;
use Illuminate\Console\Command;

class FillMeters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meters:fill
                {--meter= : Specify the id of a specific meter }
                {--type=  : Specify a meter type (water, gas, power) }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills meter data table by sampling mya archiver';

    protected function getMeters()
    {
        if ($this->option('meter')) {
            return Meter::where('id', $this->option('meter'))->get();
        }

        if ($this->option('type')) {
            return Meter::where('type', $this->option('type'))->get();
        }

        return Meter::all();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        ini_set('memory_limit', '1G');
        if ($this->option('meter') && $this->option('type')) {
            $this->error('Please specify either meter OR type, not both!');

            return false;
        }

        foreach ($this->getMeters() as $meter) {
            if ($meter->type == 'power' || $meter->type == 'water' || $meter->type == 'gas') {
                try {
                    $count = $meter->fillDataTable();
                    $this->info('Filled '.$meter->name."with $count rows");
                    $eventCount = $meter->makeNewRolloverEvents();
                    if ($eventCount > 0) {
                        $this->info("Identified $eventCount rollover events");
                    }
                    $dataUpdates = $meter->applyRolloverEvents();
                    if ($dataUpdates > 0) {
                        $this->info("Updated $dataUpdates rows with rollover_accumulation");
                    }
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        return true;
    }
}
