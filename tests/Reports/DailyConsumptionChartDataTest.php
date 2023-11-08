<?php

namespace Tests\Reports;

use App\Charts\DailyConsumption;
use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

final class DailyConsumptionChartDataTest extends TestCase
{
    /**
     * @var Meter
     */
    protected $meter;

    /**
     * @var Building;
     */
    protected $building;

    protected function setUp(): void
    {
        parent::setUp();
        $this->building = Building::factory()->create([
            'type' => 'Building',
            'begins_at' => Carbon::yesterday()->subDay(30),
        ]);
        $this->meter = Meter::factory()->create([
            'type' => 'power',
            'building_id' => $this->building->id,
            'begins_at' => Carbon::yesterday()->subDay(30),
        ]);
        $this->building->load('meters');
        $totkWh = 0;
        // Populate 10 days of data with totkWh that increment by 10/hour for the first 5 days
        // and then by 15/hour
        for ($i = 10; $i > 0; $i--) {
            for ($hour = 0; $hour < 24; $hour++) {
                $this->meter->dataTable()->insert(['meter_id' => $this->meter->id, 'date' => Carbon::today()->subDay($i)->hour($hour), 'totkWh' => $totkWh]);
                $this->building->dataTable()->insert(['building_id' => $this->building->id, 'date' => Carbon::today()->subDay($i)->hour($hour), 'totkWh' => $totkWh]);
                if ($i > 5) {
                    $totkWh += 10;
                } else {
                    $totkWh += 15;
                }
            }
        }
    }

    public function test_it_returns_correct_chart_data_for_meter(): void
    {
        $chart = new DailyConsumption($this->meter, 'totkWh');

        // Request for first day of data should yield 240
        $chart->applyRequest(new Request([
            'start' => Carbon::today()->subDay(10)->hour(0),
            'end' => Carbon::today()->subDay(9)->hour(0),
        ]));
        $data = $chart->chartData();
        // The chart data timestamp was multiplied by 1000 per javascript convention
        $this->assertEquals(Carbon::today()->subDay(10)->hour(0)->timestamp * 1000, $data->first()->x);
        $this->assertEquals(240, $data->first()->y);

        // Request for 6th day of data should yield 360
        $chart->applyRequest(new Request([
            'start' => Carbon::today()->subDay(4)->hour(0),
            'end' => Carbon::today()->subDay(3)->hour(0),
        ]));
        $data = $chart->chartData();
        // The chart data timestamp was multiplied by 1000 per javascript convention
        $this->assertEquals(Carbon::today()->subDay(4)->hour(0)->timestamp * 1000, $data->first()->x);
        $this->assertEquals(360, $data->first()->y);

        // The end parameter is not required
        $chart->applyRequest(new Request([
            'start' => Carbon::today()->subDay(4)->hour(0),
        ]));
        $data = $chart->chartData();
        // The chart data timestamp was multiplied by 1000 per javascript convention
        $this->assertEquals(Carbon::today()->subDay(4)->hour(0)->timestamp * 1000, $data->first()->x);
        $this->assertEquals(360, $data->first()->y);
    }

    public function test_it_returns_correct_chart_data_for_building(): void
    {
        $chart = new DailyConsumption($this->building, 'totkWh');

        // Request for first day of data should yield 240
        $chart->applyRequest(new Request([
            'start' => Carbon::today()->subDay(10)->hour(0),
            'end' => Carbon::today()->subDay(9)->hour(0),
        ]));
        $data = $chart->chartData();
        // The chart data timestamp was multiplied by 1000 per javascript convention
        $this->assertEquals(Carbon::today()->subDay(10)->hour(0)->timestamp * 1000, $data->first()->x);
        $this->assertEquals(240, $data->first()->y);

        // Request for 6th day of data should yield 360
        $chart->applyRequest(new Request([
            'start' => Carbon::today()->subDay(4)->hour(0),
            'end' => Carbon::today()->subDay(3)->hour(0),
        ]));
        $data = $chart->chartData();
        // The chart data timestamp was multiplied by 1000 per javascript convention
        $this->assertEquals(Carbon::today()->subDay(4)->hour(0)->timestamp * 1000, $data->first()->x);
        $this->assertEquals(360, $data->first()->y);

        // The end parameter is not required
        $chart->applyRequest(new Request([
            'start' => Carbon::today()->subDay(4)->hour(0),
        ]));
        $data = $chart->chartData();
        // The chart data timestamp was multiplied by 1000 per javascript convention
        $this->assertEquals(Carbon::today()->subDay(4)->hour(0)->timestamp * 1000, $data->first()->x);
        $this->assertEquals(360, $data->first()->y);
    }
}
