<?php

namespace Tests\Models\Meters;

use App\Models\Meters\Meter;
use App\Models\Meters\VirtualMeter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class VirtualMeterTest extends TestCase
{
    public function test_meters_relation()
    {
        $meter1 = Meter::factory()->create(['type' => 'power', 'model_number' => 'ModelX']);
        $meter2 = Meter::factory()->create(['type' => 'power', 'model_number' => 'ModelY']);
        $vm = new VirtualMeter(['name' => 'foobar']);
        $this->assertTrue($vm->save());

        $vm->physicalMeters()->attach([$meter1->id, $meter2->id]);
        $vm->load('physicalMeters');

        $this->assertEquals(2, $vm->meters()->count());
        $this->assertEquals('power', $vm->type());
    }

    public function test_it_has_data()
    {
        $meter1 = Meter::factory()->create([
            'type' => 'water',
            'begins_at' => Carbon::yesterday()->subDay(2),
        ]);
        $meter1->dataTable()->insert(['meter_id' => $meter1->id, 'date' => Carbon::yesterday()->hour(1), 'gal' => 125]);
        $meter1->dataTable()->insert(['meter_id' => $meter1->id, 'date' => Carbon::yesterday()->hour(2), 'gal' => 0]);
        $meter1->dataTable()->insert(['meter_id' => $meter1->id, 'date' => Carbon::yesterday()->hour(3), 'gal' => 250]);

        $vm = VirtualMeter::factory()->create();

        $vm->physicalMeters()->attach([$meter1->id]);
        $vm->load('physicalMeters');

        $this->assertTrue($vm->hasData());
        $this->assertEquals(Carbon::yesterday()->hour(3)->format('Y-m-d H:i:s'), $vm->lastDataDate()->date);
    }

    public function test_it_can_set_meters_explicitly()
    {
        $meter1 = Meter::factory()->create([
            'type' => 'water',
            'begins_at' => Carbon::yesterday()->subDay(2),
        ]);
        $meter1->dataTable()->insert(['meter_id' => $meter1->id, 'date' => Carbon::yesterday()->hour(1), 'gal' => 125]);
        $meter1->dataTable()->insert(['meter_id' => $meter1->id, 'date' => Carbon::yesterday()->hour(2), 'gal' => 0]);
        $meter1->dataTable()->insert(['meter_id' => $meter1->id, 'date' => Carbon::yesterday()->hour(3), 'gal' => 250]);
        $meter2 = Meter::factory()->create(['type' => 'water', 'model_number' => 'ModelY']);
        $vm = new VirtualMeter();

        $vm->setMeters(new Collection([$meter1, $meter2]));
        $this->assertTrue($vm->hasData());
    }
}
