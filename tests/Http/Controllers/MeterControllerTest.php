<?php

namespace Tests\Http\Controllers;

use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MeterControllerTest extends TestCase
{
    public function test_index()
    {
        $response = $this->get(route('meters.index'));
        $response->assertViewIs('meters.index');
        $response->assertViewHas('meters');

    }

    public function test_show()
    {
        $meter = Meter::factory()->create(['type' => 'power']);
        $response = $this->get(route('meters.show',[$meter->id]));
        $response->assertViewIs('meters.item');
        $response->assertViewHas('meter');

        $fetched = $this->getResponseData($response, 'meter');

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
        $meter = Meter::factory()->create(['type' => 'power']);
        $response = $this->get(route('meters.show',['meter' => $meter->id, 'month'=>6, 'year' => 2017]));

        $response->assertViewHas('meter');

        $fetched = $this->getResponseData($response, 'meter');

        // The reporter should default to the current month
        $this->assertEquals('2017-06-01', $fetched->reporter()->beginsAt());
        $this->assertEquals('2017-07-01', $fetched->reporter()->endsAt());

    }
}
