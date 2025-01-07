<?php

namespace Tests\Models\DataTables;

use App\Models\DataTables\DataTableReporter;
use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class DataTableReporterTest extends TestCase
{
    protected $sampleData = [
        ['date' => '2017-07-01 00:00', 'totkWh' => '10'],
        ['date' => '2017-07-01 04:00', 'totkWh' => '20'],
        ['date' => '2017-07-01 08:00', 'totkWh' => '25'],
        ['date' => '2017-07-01 12:00', 'totkWh' => '30'],
        ['date' => '2017-07-01 16:00', 'totkWh' => '40'],
        ['date' => '2017-07-01 20:00', 'totkWh' => '50'],
        ['date' => '2017-07-02 00:00', 'totkWh' => '60'],
        ['date' => '2017-07-02 04:00', 'totkWh' => '80'],
        ['date' => '2017-07-02 08:00', 'totkWh' => '95'],
        ['date' => '2017-07-02 12:00', 'totkWh' => '10'],
        ['date' => '2017-07-02 16:00', 'totkWh' => '20'],
        ['date' => '2017-07-02 20:00', 'totkWh' => '50'],
        ['date' => '2017-07-03 00:00', 'totkWh' => '80'],
    ];

    protected $sampleCollection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sampleCollection = new Collection;
        foreach ($this->sampleData as $datum) {
            $this->sampleCollection->push((object) $datum);
        }
    }

    public function test_get_accessor(): void
    {
        $meter = Meter::factory()->create(['type' => 'power']);
        $reporter = new DataTableReporter($meter);
        $this->assertInstanceOf(Carbon::class, $reporter->begins_at);
        $this->assertInstanceOf(Carbon::class, $reporter->ends_at);
    }

    public function test_begins_at(): void
    {
        $meter = Meter::factory()->create(['type' => 'power']);
        $reporter = new DataTableReporter($meter);
        $this->assertTrue((is_string($reporter->beginsAt())));
        $this->assertTrue((is_string($reporter->endsAt())));
    }

    public function test_default_dates(): void
    {
        $meter = Meter::factory()->create(['type' => 'power']);
        $reporter = new DataTableReporter($meter);
        $expectedStart = Carbon::now()->day(1)->hour(0)->minute(0)->second(0)->microsecond(0);
        $this->assertEquals($expectedStart->format('Y-m-d'), $reporter->beginsAt());
        $this->assertEquals(Carbon::tomorrow()->startOfDay()->format('Y-m-d'), $reporter->endsAt());
    }

    public function test_it_returns_data_for_day(): void
    {
        $meter = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'epics_name' => 'en1']);
        $reporter = new DataTableReporter($meter);
        $data = $reporter->dataForDay($this->sampleCollection, new Carbon('2017-07-01'));
        $this->assertCount(6, $data);

        $this->assertEquals(strtotime('2017-07-01 00:00'), strtotime($data->first()->date));
        $this->assertEquals(strtotime('2017-07-01 20:00'), strtotime($data->last()->date));

        $data = $reporter->dataForDay($this->sampleCollection, new Carbon('2017-06-01'));
        $this->assertCount(0, $data);
    }

    public function test_it_calculates_odometer_difference(): void
    {
        $meter = Meter::factory()->create(['type' => 'power', 'name' => 'm1', 'epics_name' => 'en1']);
        $reporter = new DataTableReporter($meter);
        $this->assertEquals(5, $reporter->odometerDifference(25, 30));
        $this->assertEquals(20, $reporter->odometerDifference(85, 5, 100));
    }

    public function test_first_and_last_data(): void
    {
        $meter = Meter::factory()->create(['type' => 'power']);
        $this->insertSampleData($meter);
        $reporter = new DataTableReporter($meter);
        $reporter->beginning('2017-07-01');
        $reporter->ending('2017-07-02');
        $this->assertEquals(strtotime('2017-07-01 00:00'), strtotime($reporter->firstData()->date));
        $this->assertEquals(strtotime('2017-07-02 00:00'), strtotime($reporter->lastData()->date));
    }

    protected function insertSampleData(Meter $meter)
    {
        foreach ($this->sampleData as $datum) {
            $datum['meter_id'] = $meter->id;
            $meter->dataTable()->insert($datum);
        }
    }
}
