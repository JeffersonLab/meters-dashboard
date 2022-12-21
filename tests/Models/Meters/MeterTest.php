<?php

namespace Tests\Models\Meters;

use App\Models\Buildings\Building;
use App\Models\DataTables\DataTableReporter;
use App\Models\Meters\Meter;
use App\Models\Meters\MeterLimit;
use App\Models\Meters\RolloverEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MeterTest extends TestCase
{


    /**
     * @test
     * @return void
     */
    public function it_casts_dates()
    {
        $model = Meter::factory()->create([
            'begins_at' => '2022-01-01',
            'deleted_at' => '2022-02-01',
        ]);
        // updated_at and created_at are standard eloquent and therefore did
        // not have to be specified above.
        $this->assertInstanceOf(Carbon::class, $model->created_at);
        $this->assertInstanceOf(Carbon::class, $model->updated_at);
        $this->assertInstanceOf(Carbon::class, $model->begins_at);
        $this->assertInstanceOf(Carbon::class, $model->deleted_at);
    }

    /**
     * @test
     */
    public function it_retrieves_meter_using_from_pv_scope()
    {


        $model = Meter::factory()->create([
            'epics_name' => 'foo',
            'type' => 'power',
        ]);
        $this->assertEquals($model->name, Meter::fromPv('foo:totkW')->first()->name);
        $this->assertNull(Meter::fromPv('foo:DoesNotExist')->first());
    }

    /**
     * @test
     */
    public function it_returns_pv_fields()
    {
        $meter = Meter::factory()->make(['name' => 'foo', 'epics_name' => 'bar', 'type' => 'power']);
        $this->assertCount(4, $meter->pvFields());
        $this->assertContains(':totkW', $meter->pvFields());
        $this->assertContains(':totkWh', $meter->pvFields());
        $this->assertContains(':totMBTU', $meter->pvFields());
        $this->assertContains(':llVolt', $meter->pvFields());
    }

    /**
     * @test
     */
    public function it_relates_to_building()
    {
        $building = Building::factory()->create();
        $meter = Meter::factory()->create(['building_id' => $building->id]);
        $this->assertEquals($building->name, $meter->building->name);
    }


    /**
     * @test
     */
    public function it_returns_type_from_ced_type()
    {
        $this->assertEquals('power', Meter::typeFromCEDType('PowerMeter'));
        $this->assertEquals('power', Meter::typeFromCEDType('pOwerMETer'));
        $this->assertEquals('water', Meter::typeFromCEDType('WaterMeter'));
        $this->assertEquals('water', Meter::typeFromCEDType('wAterMETer'));
        $this->assertEquals('gas', Meter::typeFromCEDType('GasMeter'));
        $this->assertEquals('gas', Meter::typeFromCEDType('gAsMETer'));
        $this->assertNull(Meter::typeFromCEDType('foobar'));
    }

    /**
     * @test
     */
    public function it_finds_all_pv_fields()
    {
        $found = Meter::allPvFields();
        $this->assertCount(8, $found);
        $this->assertContains(':totkW', $found);
        $this->assertContains(':totkWh', $found);
        $this->assertContains(':totMBTU', $found);
        $this->assertContains(':llVolt', $found);
        $this->assertContains(':gal', $found);
        $this->assertContains(':galPerMin', $found);

    }

    /**
     * @test
     */
    public function it_finds_rollover_config()
    {
        Config::set('meters.rollover.ModelX.totkWh', 500);
        $meter1 = Meter::factory()->create(['type' => 'power', 'model_number' => 'ModelX']);
        $meter2 = Meter::factory()->create(['type' => 'power', 'model_number' => 'ModelY']);
        $this->assertTrue($meter1->hasRolloverIncrement('totkWh'));
        $this->assertFalse($meter2->hasRolloverIncrement('totkWh'));
        $this->assertEquals(500, $meter1->rolloverIncrement('totkWh'));
        $this->assertContains('totkWh', $meter1->rolloverFields());
        $this->assertEmpty($meter2->rolloverFields());
    }


    /**
     * @test
     */
    public function it_has_rollover_events()
    {
        Config::set('meters.rollover.ModelX.totkWh', 500);
        $meter1 = Meter::factory()->create(['type' => 'power', 'model_number' => 'ModelX']);
        $event = new RolloverEvent([
            'meter_id' => $meter1->id,
            'field' => 'totkWh',
            'rollover_at' => Carbon::now(),
            'rollover_accumulated' => $meter1->rolloverIncrement('totkWh')
        ]);
        $this->assertTrue($event->save());
        $meter1->fresh();
        $found = $meter1->rolloverEvents;
        $this->assertCount(1, $found);
        $this->assertEquals($meter1->id, $found->first()->meter->id);
    }


    /**
     * @test
     */
    public function test_it_finds_epics_name_in_pv()
    {
        $this->assertEquals('foo', Meter::epicsNameFromPv('foo:totkW'));
        $this->assertEquals('bar:totkW', Meter::epicsNameFromPv('bar:totkW:totkW'));
        $this->assertNull(Meter::epicsNameFromPv('alligator'));

    }

    /**
     * @test
     */
    public function test_it_finds_meter_by_pv()
    {
        $meter1 = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'epics_name' => 'en1']);
        $meter2 = Meter::factory()->create(['type' => 'power', 'name' => 'm2', 'epics_name' => 'en2']);

        $found = (new Meter)->findByPv('en1:totkW');
        $this->assertEquals($meter1->id, $found->id);

        $notFound = (new Meter)->findByPv('troobar:totkW');
        $this->assertNull($notFound);

    }

    /**
     * @test
     */
    public function test_it_obtains_reporter()
    {
        $meter1 = Meter::factory()->create(['type' => 'power']);
        $this->assertInstanceOf(DataTableReporter::class, $meter1->reporter());
    }


    /**
     * @test
     */
    public function test_it_is_precluded_from_duplicate_epics_name()
    {
        //TODO implement afer database migration update
        $this->assertTrue(true);  //placeholder
    }


    /**
     * @test
     */
    public function test_it_retrieves_limits()
    {
        $meter = Meter::factory()->create(['type' => 'water', 'name' => 'm1', 'epics_name' => 'en1']);
        $limit = new MeterLimit([
            'meter_id' => $meter->id,
            'field' => 'gal',
            'interval' => 1,
            'lolo' => 0,
            'hihi' => 100,
            'source' => 'epics'
        ]);
        $this->assertTrue($limit->save());
        $meter->fresh();
        $this->assertTrue($meter->hasMeterLimits());
        $this->assertTrue($meter->hasMeterLimits('gal'));
        $this->assertFalse($meter->hasMeterLimits('kWh'));
        $this->assertEquals(1, $meter->meterLimits->count());
        $this->assertEquals(100, $meter->meterLimits->first()->hihi);

        $this->assertTrue($meter->withinLimits('gal', 50));
        $this->assertFalse($meter->withinLimits('gal', 0));
        $this->assertFalse($meter->withinLimits('gal', 100));

    }

    /**
     * @test
     */
    public function test_it_respects_major_and_minor_limits()
    {
        $meter = Meter::factory()->create(['type' => 'water', 'name' => 'm1', 'epics_name' => 'en1']);
        $limit = new MeterLimit([
            'meter_id' => $meter->id,
            'field' => 'gal',
            'interval' => 1,
            'lolo' => 0,
            'low' => 20,
            'high' => 80,
            'hihi' => 100,
            'source' => 'epics'
        ]);
        $this->assertTrue($limit->save());
        $meter->fresh();
        $this->assertTrue($meter->hasMeterLimits());
        $this->assertTrue($meter->hasMeterLimits('gal'));
        $this->assertFalse($meter->hasMeterLimits('kWh'));
        $this->assertEquals(1, $meter->meterLimits->count());
        $this->assertEquals(100, $meter->meterLimits->first()->hihi);


        $this->assertTrue($meter->withinLimits('gal', 50));
        $this->assertFalse($meter->withinLimits('gal', 10)); // less than minor low
        $this->assertFalse($meter->withinLimits('gal', 90)); // more than minor high

    }

    function test_it_performs_first_and_last_data_queries(){
        $meter = Meter::factory()->create(['type' => 'water', 'begins_at' => Carbon::yesterday()->subDay(5)]);

        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(9), 'gal' => 100]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(8), 'gal' => 200]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(1), 'gal' => 900]);

        $this->assertEquals(100, $meter->firstDataQuery('gal')->first()->gal);
        $this->assertEquals(900, $meter->lastDataQuery('gal')->first()->gal);
    }


    /**
     * @test
     */
    function test_it_returns_average_for_an_interval()
    {
        $meter = Meter::factory()->create(['type' => 'water', 'begins_at' => Carbon::yesterday()->subDay(2)]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::yesterday()->hour(1), 'gal' => 125]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::yesterday()->hour(2), 'gal' => 0]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::yesterday()->hour(3), 'gal' => 250]);
        $stats = $meter->statsBetween('gal', Carbon::yesterday()->hour(1), Carbon::yesterday()->hour(3));
        $this->assertEquals(125, $stats->avg);
        $this->assertEquals(0, $stats->min);
        $this->assertEquals(250, $stats->max);
    }

    /**
     * @test
     */
    function test_it_returns_first_on_or_after()
    {
        $meter = Meter::factory()->create(['type' => 'water', 'begins_at' => Carbon::yesterday()->subDay(5)]);

        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(3), 'gal' => 125]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(2), 'gal' => 250]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(1), 'gal' => 375]);

        $data = $meter->firstDataOnOrAfter('gal', Carbon::today()->subDay(5));
        $this->assertEquals(Carbon::today()->subDays(3), $data->date);
        $this->assertEquals(125, $data->gal);

        $data = $meter->firstDataOnOrAfter('gal', Carbon::today()->subDay(2));
        $this->assertEquals(Carbon::today()->subDays(2), $data->date);
        $this->assertEquals(250, $data->gal);

    }


    /**
     * @test
     */
    function test_it_makes_and_applies_rollover_events()
    {
        Config::set('meters.rollover.ModelX.totkWh', 1000);
        $meter = Meter::factory()->create([
            'type' => 'power',
            'begins_at' => Carbon::yesterday()->subDay(10),
            'model_number' => 'ModelX',
        ]);
        $this->assertCount(0, $meter->rolloverEvents);
        $this->assertEquals(1000, $meter->rolloverIncrement('totkWh'));
        $this->assertContains('totkWh', $meter->rolloverFields());

        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(9), 'totkWh' => 125]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(8), 'totkWh' => 250]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(7), 'totkWh' => 775]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(6), 'totkWh' => 5]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(5), 'totkWh' => 345]);

        $meter->makeNewRolloverEvents();
        $meter->load('rolloverEvents');  // forces collection to reload from db

        $this->assertCount(1, $meter->rolloverEvents);
        $event = $meter->lastRolloverEvent('totkWh');
        $this->assertEquals(1000, $event->rollover_accumulated);
        $this->assertEquals(Carbon::today()->subDays(6), $event->rollover_at);

        $meter->applyRolloverEvents();
        $data = $meter->dataTable()->select('*')->orderBy('date')->get();
        $this->assertEquals(125, $data->first()->totkWh);
        $this->assertEquals(1345, $data->last()->totkWh);


    }


    /**
     * @test
     */
    function test_it_returns_first_or_last_between()
    {
        $meter = Meter::factory()->create(['type' => 'water', 'begins_at' => Carbon::yesterday()->subDay(5)]);

        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(9), 'gal' => 100]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(8), 'gal' => 200]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(7), 'gal' => 300]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(6), 'gal' => 400]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(5), 'gal' => 500]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(4), 'gal' => 600]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(3), 'gal' => 700]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(2), 'gal' => 800]);
        $meter->dataTable()->insert(['meter_id' => $meter->id, 'date' => Carbon::today()->subDays(1), 'gal' => 900]);

        $data = $meter->firstDataBetween('gal', Carbon::today()->subDay(7), Carbon::today()->subDay(5));
        $this->assertEquals(Carbon::today()->subDays(7), $data->date);
        $this->assertEquals(300, $data->gal);

        $data = $meter->lastDataBetween('gal', Carbon::today()->subDay(7), Carbon::today()->subDay(5));
        $this->assertEquals(Carbon::today()->subDays(5), $data->date);
        $this->assertEquals(500, $data->gal);

    }





}
