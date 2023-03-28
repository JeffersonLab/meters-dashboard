<?php

namespace Tests\Models\Buildings;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Carbon\Carbon;
use Tests\TestCase;

class BuildingTest extends TestCase
{
    public function test_it_returns_pv_fields(): void
    {
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id' => $building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id' => $building->id]);

        $this->assertCount(5, $building->pvFields());
        $this->assertContains(':totkW', $building->pvFields());
        $this->assertContains(':totkWh', $building->pvFields());
        $this->assertContains(':totMBTU', $building->pvFields());
        $this->assertContains(':gal', $building->pvFields());
        $this->assertContains(':galPerMin', $building->pvFields());
        $this->assertNotContains(':llVolt', $building->pvFields());
    }

    public function test_it_return_channels(): void
    {
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id' => $building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id' => $building->id]);

        $this->assertCount(5, $building->channels());
        $this->assertContains('foo:totkW', $building->channels());
        $this->assertContains('foo:totkWh', $building->channels());
        $this->assertContains('foo:gal', $building->channels());
        $this->assertContains('foo:totMBTU', $building->channels());
    }

    public function test_meter_types(): void
    {
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id' => $building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id' => $building->id]);

        $this->assertTrue($building->hasMeterType('power'));
        $this->assertTrue($building->hasMeterType('water'));
        $this->assertFalse($building->hasMeterType('gas'));
    }

    public function test_meters_of_type(): void
    {
        $building = Building::factory()->create(['name' => 'foo']);
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'building_id' => $building->id]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'name' => 'm2', 'building_id' => $building->id]);

        $wm = $building->metersOfType('water');
        $this->assertEquals($meter2->name, $wm->first()->name);
        $waterMeter = $building->waterMeters()->first();
        $this->assertEquals($meter2->name, $waterMeter->name);
    }

    public function test_it_fetches_supply_and_drain_meters(): void
    {
        $building = Building::factory()->create(['name' => 'CoolingTower']);
        foreach (['CT-SUPPLY-1', 'CT-SUPPLY-2', 'CT-SUPPLY-3', 'CT-DRAIN1', 'CT-FLTERDRAIN-1'] as $name) {
            Meter::factory()->create(['type' => 'water', 'epics_name' => $name, 'building_id' => $building->id]);
        }
        $building->load('meters');
        $this->assertCount(5, $building->waterMeters()->get());
        $this->assertCount(3, $building->waterSupplyMeters()->get());
        $this->assertCount(2, $building->waterDrainMeters()->get());
    }

    public function test_it_computes_consumption_sewer_evaporation(): void
    {
        $building = Building::factory()->create(['name' => 'CoolingTower']);
        $supplyMeter1 = Meter::factory()->create(['type' => 'water', 'epics_name' => 'CT-SUPPLY-1', 'building_id' => $building->id]);
        $supplyMeter2 = Meter::factory()->create(['type' => 'water', 'epics_name' => 'CT-SUPPLY-2', 'building_id' => $building->id]);
        $drainMeter1 = Meter::factory()->create(['type' => 'water', 'epics_name' => 'CT-DRAIN-1', 'building_id' => $building->id]);

        // Dates we'll use for the test
        $begin = Carbon::today()->subDay(2)->hour(0);
        $end = Carbon::today()->subDay(1)->hour(0);

        // Data for 100 gal of consumption
        $supplyMeter1->dataTable()->insert(['meter_id' => $supplyMeter1->id, 'date' => $begin, 'gal' => 100]);
        $supplyMeter1->dataTable()->insert(['meter_id' => $supplyMeter1->id, 'date' => $end, 'gal' => 200]);
        $this->assertEquals(100, $supplyMeter1->consumedBetween('gal', $begin, $end));

        // Data for 150 gal of consumption
        $supplyMeter2->dataTable()->insert(['meter_id' => $supplyMeter2->id, 'date' => $begin, 'gal' => 0]);
        $supplyMeter2->dataTable()->insert(['meter_id' => $supplyMeter2->id, 'date' => $end, 'gal' => 150]);
        $this->assertEquals(150, $supplyMeter2->consumedBetween('gal', $begin, $end));

        // Data for 50 gal of consumption
        $drainMeter1->dataTable()->insert(['meter_id' => $drainMeter1->id, 'date' => $begin, 'gal' => 500]);
        $drainMeter1->dataTable()->insert(['meter_id' => $drainMeter1->id, 'date' => $end, 'gal' => 550]);
        $this->assertEquals(50, $drainMeter1->consumedBetween('gal', $begin, $end));

        // Now the various calculated values for the building
        $this->assertEquals(250, $building->waterConsumption($begin, $end));  // Sum of Supply
        $this->assertEquals(50, $building->waterToSewer($begin, $end));       // Sum of Drain
        $this->assertEquals(200, $building->waterToEvaporation($begin, $end)); // Supply - Drain
        $this->assertEquals(5, $building->waterCyclesOfConcentration($begin, $end)); // Supply / Drain
    }
}
