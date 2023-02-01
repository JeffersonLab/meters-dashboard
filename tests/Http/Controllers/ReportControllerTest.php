<?php

namespace Tests\Http\Controllers;

use App\Models\Meters\Meter;
use Carbon\Carbon;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    protected $meter1;

    protected $meter2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->meter1 = Meter::factory()->create([
            'name' => 'name 1',
            'epics_name' => 'epics 1',
            'name_alias' => 'alias 1',
            'type' => 'power',
            'begins_at' => Carbon::yesterday()->subDay(5)->hour(config('reports.day_start_hour')),
            'model_number' => 'ModelX',
        ]);

        $this->fillMeterWithData($this->meter1, 100, 0);

        $this->meter2 = Meter::factory()->create([
            'name' => 'name 2',
            'epics_name' => 'epics 2',
            'name_alias' => 'alias 2',
            'type' => 'power',
            'begins_at' => Carbon::yesterday()->subDay(4)->hour(config('reports.day_start_hour')),
            'model_number' => 'ModelX',
        ]);

        $this->fillMeterWithData($this->meter2, 10, 25);
    }

    public function test_show()
    {
//        dd(config('reports.day_start_hour'));
        $start = Carbon::today()->subDays(5)->format('Y-m-d');
        $end = Carbon::today()->format('Y-m-d');

        $response = $this->call(
            'GET',
            route('reports.item', ['power-consumption']),
            ['begin' => $start, 'end' => $end, 'meters' => implode(',', [$this->meter1->epics_name, $this->meter2->epics_name])]
        );
        $response->assertViewIs('reports.consumption');
        $response->assertViewHas('report');

        $response->assertViewHas('excelUrl');

        $fetched = $this->getResponseData($response, 'report');
        $this->assertCount(2, $fetched->data());  // There are two test meters
        $datum = $fetched->data()->first();
        //dd($datum);

        $this->assertEquals($this->meter1->name, $datum->meter->name);

        $this->assertEquals(0, $datum->first->totkWh);
        $this->assertEquals(500, $datum->last->totkWh);
        $this->assertEquals(500, $datum->consumed);

        //TODO reimplement excel export
//        $excelUrl = $this->getResponseData($response, 'excelUrl');
//        $response = $this->get($excelUrl);
//        $response->assertStatus(200);
//        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_fill_meter_with_data($meter, $increment = 100, $initial = 0)
    {
        $meter->dataTable()->where('meter_id', $meter->id)->delete();
        $date = clone $meter->begins_at;
        $val = $initial;
        while ($date < Carbon::today()) {
            $date->addDay();
            $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => $date, 'totkWh' => $val]);
            $val += $increment;
        }
    }
}
