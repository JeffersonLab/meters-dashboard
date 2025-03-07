<?php

namespace Tests;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    //    use RefreshDatabase;

    // The values for meters.pvs config
    protected $pvs = [
        'power' => [
            ':totkW' => [
                'description' => 'Total real power',
                'units' => 'kW',
            ],
            ':totkWh' => [
                'description' => 'Total real energy',
                'units' => 'kWh',
            ],
            ':totMBTU' => [
                'description' => 'Total real energy',
                'units' => 'MBTU',
            ],
            ':llVolt' => [
                'description' => 'Average Voltage',
                'units' => 'V',
            ],
        ],
        'water' => [
            ':gal' => [
                'description' => 'Total Gallons',
                'units' => 'gallons',
            ],
            ':galPerMin' => [
                'description' => 'Flow Rate',
                'units' => 'gpm',
            ],
        ],

        'gas' => [
            ':ccf' => [
                'description' => 'Total CCF',
                'units' => 'ccf',
            ],
            ':ccfPerMin' => [
                'description' => 'Flow Rate',
                'units' => 'ccfpm',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // Must delete meters first because they reference building via building_id FK.
        foreach (Meter::withTrashed()->get()->all() as $meter) {
            $meter->forceDelete();
        }
        foreach (Building::withTrashed()->get()->all() as $building) {
            $building->forceDelete();
        }
        //specify the valid epics field names for a power meter
        Config::set('meters.pvs', $this->pvs);
    }

    /**
     * Ensure that we call delete on all created meters and buildings so that they can
     * remove their associated data table.
     */
    protected function tearDown(): void
    {
        // Must delete meters first because they reference building via building_id FK.
        foreach (Meter::withTrashed()->get()->all() as $meter) {
            $meter->forceDelete();
        }
        foreach (Building::withTrashed()->get()->all() as $building) {
            $building->forceDelete();
        }
        parent::tearDown();
    }

    /**
     * Extract a variable from a response.
     *
     * For example a variable sent to a view.
     *
     * @return mixed
     */
    protected function getResponseData($response, $key)
    {
        $content = $response->getOriginalContent();
        $data = $content->getData();

        return $data[$key];
    }
}
