<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meter Data
    |--------------------------------------------------------------------------
    |
    | This file is for storing configuration details of the Meters in EPICS
    |
    */
    // The interval in seconds between data points saved in the
    // meters data tables (power_meter_data, water_meter_data, etc.)
    'data_interval' => 300,

    'alert_email_recipients' => [
        'theo@jlab.org',
        'gcroke@jlab.org',
    ],

    // Documents the fields that can be appended to a meter's epics_name
    // to produce a valid PV.  They are grouped by meter type.
    'pvs' => [
        'power' => [
            ':totkW' => [
                'description' => 'Total real power',
                'units' => 'kW',
            ],
            ':totkWh' => [
                'description' => 'Total real energy',
                'units' => 'kWh',
            ],
            // ':totMBTU' => [
            //     'description' => 'Total real energy',
            //     'units'      =>  'MBTU'
            // ],
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
    ],

    // Substation Meters
    // The meters below identified by their epics_name
    // are large sub-station scale meters
    // TODO replace this static config w/epics lookups.
    'substation' => [
        '33MVA',
        '40MVA',
        '40MVA_22MVA_Tie',
        '40MVA_North_East_Loop',
        '40MVA_33MVA_Tie',
        '40MVA_End_Station_Loop',
        '40MVA_CHL1_Loop',
        '40MVA_North_West_Loop',
        '40MVA_South_Loop',
    ],

    // The font-awesome icons & attributes to use when displaying
    // a given meter type.
    'icons' => [
        'power' => ['color' => 'red', 'symbol' => 'fas fa-fw fa-bolt'],
        'water' => ['color' => 'aqua', 'symbol' => 'fas fa-fw fa-tint'],
        'gas' => ['color' => 'yellow', 'symbol' => 'fas fa-fw fa-cloud'],
        'cooling_tower' => ['color' => 'aqua', 'symbol' => 'fas fa-fw fa-building'],
    ],

    // Documents the value at which "rollover" occurs for a
    // particular model of meter.
    'rollover' => [
        'PXM2250' => [
            'totkWh' => 1000000,
        ],
        'Shark50B' => [
            'totkWh' => 100000000,
        ],

    ],

];
