<?php

namespace Tests\Http\Controllers;

use App\Models\Meters\Meter;
use Carbon\Carbon;
use Tests\TestCase;

class MeterControllerTest extends TestCase
{
    public function test_index(): void
    {
        $response = $this->get(route('meters.index'));
        $response->assertViewIs('meters.index');
        $response->assertViewHas('meters');
    }

    public function test_show(): void
    {
        $meter = Meter::factory()->create(['type' => 'power']);
        $response = $this->get(route('meters.show', [$meter->id]));
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
}
