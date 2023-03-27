<?php

namespace App\Console\Commands;

use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class UpdateMeters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meters:update
                {--meter= : Specify the id of a specific meter }
                {--type=  : Specify a meter type (water, gas, power) }
                {begin : Specify starting date }
                {pvs* : PVs to update. Avoid updating pvs susceptible to rollover}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates meter data table by sampling mya archiver';

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

    protected function beginAt()
    {
        if ($this->argument('begin')) {
            return Carbon::parse($this->argument('begin'));
        }
    }

    protected function pvs()
    {
        return Arr::wrap($this->argument('pvs'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        if ($this->option('meter') && $this->option('type')) {
            $this->error('Please specify either meter OR type, not both!');

            return false;
        }

        foreach ($this->getMeters() as $meter) {
            if ($meter->type == 'power' || $meter->type == 'water' || $meter->type == 'gas') {
                try {
                    $count = $meter->updateDataTable($this->beginAt(), $this->pvs());
                    $this->info("Updated $count rows for ".$meter->name);

//                    $eventCount = $meter->makeNewRolloverEvents();
//                    if ($eventCount > 0) {
//                        $this->info("Identified $eventCount rollover events");
//                    }
//                    $dataUpdates = $meter->applyRolloverEvents();
//                    if ($dataUpdates > 0) {
//                        $this->info("Updated $dataUpdates rows with rollover_accumulation");
//                    }
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        return true;
    }
}
