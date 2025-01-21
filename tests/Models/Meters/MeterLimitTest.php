<?php

namespace Tests\Models\Meters;

use App\Models\Meters\Meter;
use App\Models\Meters\MeterLimit;
use Tests\TestCase;

final class MeterLimitTest extends TestCase
{
    public function test_it_rejects_hihi_less_than_lolo(): void
    {
        $meter = Meter::factory()->create(['type' => 'water', 'name' => 'm1', 'epics_name' => 'en1']);
        $limit = new MeterLimit([
            'meter_id' => $meter->id,
            'field' => 'gal',
            'interval' => 1,
            'lolo' => 50,
            'hihi' => 40,
            'source' => 'epics',
        ]);
        $this->assertFalse($limit->save());
    }

    public function test_it_rejects_high_less_than_low(): void
    {
        $meter = Meter::factory()->create(['type' => 'water', 'name' => 'm1', 'epics_name' => 'en1']);
        $limit = new MeterLimit([
            'meter_id' => $meter->id,
            'field' => 'gal',
            'interval' => 1,
            'low' => 50,
            'high' => 40,
            'source' => 'epics',
        ]);
        $this->assertFalse($limit->save());
    }

    public function test_it_works_with_only_major(): void
    {
        $meter = Meter::factory()->create(['type' => 'water', 'name' => 'm1', 'epics_name' => 'en1']);
        $limit = new MeterLimit([
            'meter_id' => $meter->id,
            'field' => 'gal',
            'interval' => 1,
            'lolo' => 0,
            'hihi' => 100,
            'source' => 'epics',
        ]);
        $this->assertTrue($limit->save());

        $this->assertFalse($limit->hasUpperLimitMinor());
        $this->assertTrue($limit->hasUpperLimitMajor());
        $this->assertTrue($limit->hasUpperLimit());

        $this->assertFalse($limit->hasLowerLimitMinor());
        $this->assertTrue($limit->hasLowerLimitMajor());
        $this->assertTrue($limit->hasLowerLimit());

        $this->assertTrue($limit->isWithinMajorLimits(50));
        $this->assertTrue($limit->isWithinMinorLimits(50));
        $this->assertTrue($limit->isWithinLimits(50));

        $this->assertTrue($limit->isTooHigh(105));  //exceeds minor
        $this->assertTrue($limit->isTooHigh(101));  //exceeds minor
        $this->assertFalse($limit->isTooLow(0));  //exceeds minor
        $this->assertTrue($limit->isTooLow(-1));  //exceeds minor
    }

    public function test_it_works_with_minor_and_major(): void
    {
        $meter = Meter::factory()->create(['type' => 'water', 'name' => 'm1', 'epics_name' => 'en1']);
        $limit = new MeterLimit([
            'meter_id' => $meter->id,
            'field' => 'gal',
            'interval' => 1,
            'lolo' => 0.0,
            'low' => 20.0,
            'high' => 80.0,
            'hihi' => 100.0,
            'source' => 'epics',
        ]);
        $this->assertTrue($limit->save());

        $this->assertTrue($limit->hasUpperLimitMinor());
        $this->assertTrue($limit->hasUpperLimitMajor());
        $this->assertTrue($limit->hasUpperLimit());

        $this->assertTrue($limit->hasLowerLimitMinor());
        $this->assertTrue($limit->hasLowerLimitMajor());
        $this->assertTrue($limit->hasLowerLimit());

        //Exceeds none
        $this->assertTrue($limit->isWithinMajorLimits(50));
        $this->assertFalse($limit->isTooHighMinor(50));
        $this->assertFalse($limit->isTooLowMinor(50));
        $this->assertTrue($limit->isWithinMinorLimits(50));
        $this->assertTrue($limit->isWithinLimits(50));

        //Exceeds minor, not major
        $this->assertTrue($limit->isWithinMajorLimits(85));
        $this->assertFalse($limit->isWithinMinorLimits(85));
        $this->assertTrue($limit->isWithinMajorLimits(15));
        $this->assertFalse($limit->isWithinMinorLimits(15));

        $this->assertTrue($limit->isTooHigh(85));  //exceeds minor
        $this->assertTrue($limit->isTooLow(15));  //exceeds minor

        $this->assertFalse($limit->isTooHighMajor(85));  //exceeds minor
        $this->assertFalse($limit->isTooLowMajor(15));  //exceeds minor
    }
}
