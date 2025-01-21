<?php

namespace App\Console\Commands;

use App\Models\Meters\Meter;
use App\Utilities\MeterLimitImporter;
use Illuminate\Console\Command;

class ImportMeterLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meters:import-limits {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import meter limits from a CSV file placed in storage/app/imports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $l = new MeterLimitImporter();
        $l->import($this->argument('file'));
        foreach ($l->data as $row) {
          try{
              if (!empty($row)) {
                  $meter = Meter::where('epics_name', $row['meter'])->first();
                  if ($meter) {
                      $meter->meterLimits()->delete();
                      $meterLimit = $meter->meterLimits()->create([
                          'field' => $row['field'],
                          'interval' => $row['interval'],
                          'low' => $row['low'],
                          'lolo' => $row['lolo'],
                          'high' => $row['high'],
                          'hihi' => $row['hihi'],
                          'source' => $row['source'],
                      ]);
                      if ($meterLimit->save()) {
                          $this->line('Meter limit' . $row['meter'] . ' imported successfully');
                      }
                      if ($meterLimit->hasErrors()) {
                          throw new \Exception($meterLimit->errors()->first());
                      }
                  }
                  else {
                      throw new \Exception("Can't find meter " . $row['meter'] . " in DB");
                  }
              }
          } catch (\Exception $e) {
              $this->error($e->getMessage());
              return 1;
          }
        }
    }
}
