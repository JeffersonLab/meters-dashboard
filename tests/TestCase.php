<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    // The values for meters.pvs config
    protected $pvs = [
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
            ':llVolt' => [
                'description' => 'Average Voltage',
                'units' => 'V'
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


    public function setUp(): void
    {
        parent::setUp();
        //specify the valid epics field names for a power meter
        Config::set('meters.pvs', $this->pvs);
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
    protected function getResponseData($response, $key){
        $content = $response->getOriginalContent();
        $data = $content->getData();
        return $data[$key];
    }
}
