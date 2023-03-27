<?php

namespace App\Console\Commands;

use App\Models\Buildings\Building;
use App\Models\DataTables\DataTableCreator;
use App\Models\Meters\Meter;
use Illuminate\Console\Command;

class ConvertData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meters:convert {--drop : Drop and then create per-meter table }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert data from monolithic to per-meter/per-building table format';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $status = 0;
        $status += $this->convert(Building::all());
        $status += $this->convert(Meter::all());

        return $status;
    }

    protected function convert($items)
    {
        $status = 0;
        foreach ($items as $item) {
            try {
                $c = new DataTableCreator($item);
                if ($this->option('drop')) {
                    $c->dropTable();
                }
                $c->createTable();
                $c->migrateData();
                $this->info("Converted {$item->name}");
            } catch (\Exception $e) {
                $this->error("Failed to convert {$item->name}");
                $this->error($e->getMessage());
                $status = 1;
            }
        }

        return $status;
    }
}
