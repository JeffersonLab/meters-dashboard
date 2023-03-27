<?php

namespace Tests\Reports;

use App\Models\Meters\Meter;
use App\Reports\PowerConsumption;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PowerConsumptionReportTest extends TestCase
{
    protected $meter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->meter = Meter::factory()->create(['type' => 'power', 'begins_at' => Carbon::yesterday()->subDay(30)]);
        $totkWh = 0;
        // Populate 5 days of data with totkWh that increment by 10/hour
        for ($i = 5; $i > 0; $i--) {
            for ($hour = 0; $hour < 24; $hour++) {
                $this->meter->dataTable()->insert(['meter_id' => $this->meter->id, 'date' => Carbon::today()->subDay($i)->hour($hour), 'totkWh' => $totkWh]);
                $totkWh += 10;
            }
        }
    }

    public function test_it_sets_day_start_hour(): void
    {
        Config::set('reports.day_start_hour', 8);
        $r = new PowerConsumption();
        $this->assertEquals(8, $r->begins_at->hour);

        Config::set('reports.day_start_hour', 0);
        $r = new PowerConsumption();
        $this->assertEquals(0, $r->begins_at->hour);
    }

    public function test_it_reports_using_correct_default_start_hour(): void
    {
        Config::set('reports.day_start_hour', 8);
        $report = new PowerConsumption();
        $request = new \Illuminate\Http\Request([
            'begin' => Carbon::today()->subDay(5)->format('Y-m-d'),
            'meters' => $this->meter->epics_name,
        ]);
        $report->applyRequest($request);

        $item = $report->data()->first();
        $this->assertEquals(80, $item->first->totkWh);   // first day of data at 08:00
        $this->assertEquals(1190, $item->last->totkWh);  // fifth day of data at 23:00
        $this->assertEquals(1110, $item->consumed);
    }

    public function test_it_reports_using_explicit_day_start_hour(): void
    {
        // Let's test midnight to midnight reporting
        Config::set('reports.day_start_hour', 0);
        $report = new PowerConsumption();
        $request = new \Illuminate\Http\Request([
            'begin' => Carbon::today()->subDay(5)->hour(3)->format('Y-m-d H:i'),
            'end' => Carbon::today()->subDay(4)->hour(4)->format('Y-m-d H:i'),
            'meters' => $this->meter->epics_name,
        ]);
        $report->applyRequest($request);

        $item = $report->data()->first();
        $this->assertEquals(30, $item->first->totkWh);   // first day of data at 03:00
        $this->assertEquals(280, $item->last->totkWh);  // second day of data at 04:00
        $this->assertEquals(250, $item->consumed);       // 25 hours total
    }
}
