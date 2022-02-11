<?php

namespace Tests\Http\Controllers;

use App\Models\Buildings\Building;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuildingControllerTest extends TestCase
{



    public function test_index()
    {
        $response = $this->get(route('buildings.index'));
        $response->assertViewIs('buildings.index');

    }

    public function test_show()
    {
        $building = Building::factory()->create();
        $response = $this->get(route('buildings.show',[$building->id]));
        //dd($response->getContent());
        $response->assertViewIs('buildings.item');
        $response->assertViewHas('building');

        $fetched = $this->getResponseData($response, 'building');

        // The reporter should default to the current month-to-date
        $date = Carbon::now();
        $date->day(1)->hour(0)->minute(0)->second(0);
        $this->assertEquals($date->format('Y-m-d'), $fetched->reporter()->beginsAt());
        $date = Carbon::tomorrow();
        $date->hour(0)->minute(0)->second(0);
        $this->assertEquals($date->format('Y-m-d'), $fetched->reporter()->endsAt());

    }

    public function test_show_specific_month()
    {
        $building = Building::factory()->create();
        $response = $this->get(route('buildings.show',['building' => $building->id, 'month'=>6, 'year' => 2017]));

        $response->assertViewHas('building');

        $fetched = $this->getResponseData($response, 'building');

        // The reporter should default to the current month
        $this->assertEquals('2017-06-01', $fetched->reporter()->beginsAt());
        $this->assertEquals('2017-07-01', $fetched->reporter()->endsAt());

    }
}
