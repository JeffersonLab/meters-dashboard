<?php

namespace Tests\Models\Buildings;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class BuildingTest extends TestCase
{
    protected $pvsConfig = [
        'power' => [
            ':totkW' => [
                'description' => 'Total real power',
                'units' => 'kW'
            ],
            ':totkWh' => [
                'description' => 'Total real energy',
                'units' => 'kWh'
            ],
            ':totMBTU' => [
                'description' => 'Total real energy',
                'units' => 'MBTU'
            ],


        ],
        'water' => [
            ':gal' => [
                'description' => 'Total Gallons',
                'units'     => 'gallons',
            ]
        ],
        'gas' => [],
    ];


    public function test_it_returns_pv_fields()
    {
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id'=>$building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id'=>$building->id]);

        Config::set('meters.pvs', $this->pvsConfig);

        $this->assertCount(4, $building->pvFields());
        $this->assertContains(':totkW', $building->pvFields());
        $this->assertContains(':totkWh', $building->pvFields());
        //$this->assertContains(':totMBTU', $building->pvFields());
        $this->assertContains(':gal', $building->pvFields());
    }

    public function test_it_return_channels()
    {
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id'=>$building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id'=>$building->id]);

        Config::set('meters.pvs', $this->pvsConfig);

        $this->assertCount(4, $building->channels());
        $this->assertContains('foo:totkW', $building->channels());
        $this->assertContains('foo:totkWh', $building->channels());
        $this->assertContains('foo:gal', $building->channels());
    }

    public function test_meter_types(){
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id'=>$building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id'=>$building->id]);

        $this->assertTrue($building->hasMeterType('power'));
        $this->assertTrue($building->hasMeterType('water'));
        $this->assertFalse($building->hasMeterType('gas'));
    }

    public function test_meters_of_type(){
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id'=>$building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id'=>$building->id]);

        $wm = $building->metersOfType('water');
        $this->assertEquals($meter2->name, $wm->first()->name);
        $waterMeter = $building->waterMeters()->first();
        $this->assertEquals($meter2->name, $waterMeter->name);
    }

}
