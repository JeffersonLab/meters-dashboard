<?php

namespace Tests;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
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
        //specify the valid epics field names for a power meter
        Config::set('meters.pvs', $this->pvs);
    }

    /**
     * Ensure that we call delete on all created meters and buildings so that they can
     * remove their associated data table.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Must delete meters first because they reference building via building_id FK.
        foreach (Meter::all() as $meter) {
            $meter->delete();
        }
        foreach (Building::all() as $building) {
            $building->delete();
        }
        parent::tearDown();
    }

    /**
     * Extract a variable from a response.
     *
     * For example a variable sent to a view.
     *
     * @param $response
     * @param $key
     * @return mixed
     */
    protected function getResponseData($response, $key)
    {
        $content = $response->getOriginalContent();
        $data = $content->getData();

        return $data[$key];
    }
}
