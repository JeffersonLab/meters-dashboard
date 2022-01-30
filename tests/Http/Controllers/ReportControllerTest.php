<?php

namespace Tests\Http\Controllers;

use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    protected $meter1;
    protected $meter2;

    function setup(): void {
        parent::setUp();

        $this->meter1 = Meter::factory()->create([
            'name' => 'name 1',
            'epics_name' => 'epics 1',
            'name_alias' => 'alias 1',
            'type' => 'power',
            'begins_at'=>Carbon::yesterday()->subDay(5),
            'model_number' => 'ModelX',
        ]);

        $this->fillMeterWithData($this->meter1, 100, 0);

        $this->meter2 = Meter::factory()->create([
            'name' => 'name 2',
            'epics_name' => 'epics 2',
            'name_alias' => 'alias 2',
            'type' => 'power',
            'begins_at'=>Carbon::yesterday()->subDay(4),
            'model_number' => 'ModelX',
        ]);

        $this->fillMeterWithData($this->meter2, 10, 25);
    }

    public function test_show()
    {
        $start = Carbon::today()->subDays(5)->format('Y-m-d');
        $end = Carbon::today()->format('Y-m-d');

        $response = $this->call(
            'GET',
            route('reports.item',['meter-power-consumption']),
            ['start' => $start, 'end' => $end]
        );
        $response->assertViewIs('reports.item');
        $response->assertViewHas('report');
        $response->assertViewHas('excelUrl');

        $fetched = $this->getResponseData($response, 'report');
        $this->assertCount(2, $fetched->data());  // There are two test meters
        $datum = $fetched->data()->first();

        $this->assertEquals($this->meter1->name, $datum->item->name);

        $this->assertEquals(0, $datum->first->totkWh);
        $this->assertEquals(500, $datum->last->totkWh);
        $this->assertEquals(500, $datum->consumed);


        $excelUrl = $this->getResponseData($response, 'excelUrl');
        $response = $this->get($excelUrl);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }



    public function test_name_filter(){
        $start = Carbon::today()->subDays(5)->format('Y-m-d');
        $end = Carbon::today()->format('Y-m-d');

        $response = $this->call(
            'GET',
            route('reports.item',['meter-power-consumption']),
            ['start' => $start, 'end' => $end, 'names'=>'alias 1']
        );

        $response->assertViewIs('reports.item');
        $response->assertViewHas('report');
        $response->assertViewHas('excelUrl');

        $fetched = $this->getResponseData($response, 'report');
        $this->assertCount(1, $fetched->data());
        $datum = $fetched->data()->first();
        $this->assertEquals('alias 1', $datum->label);

        $response = $this->call(
            'GET',
            route('reports.item',['meter-power-consumption']),
            ['start' => $start, 'end' => $end, 'names'=>'alias 1, epics 2']
        );

        $fetched = $this->getResponseData($response, 'report');
        $this->assertCount(2, $fetched->data());


    }


    function fillMeterWithData($meter, $increment = 100, $initial = 0){
        $meter->dataTable()->where('meter_id',$meter->id)->delete();
        $date = clone $meter->begins_at;
        $val = $initial;
        while ($date < Carbon::today()){
            $date->addDay();
            $meter->dataTable()->insert(['meter_id'=>$meter->id, 'date'=>$date, 'totkWh' => $val]);
            $val += $increment;
        }

    }

}
