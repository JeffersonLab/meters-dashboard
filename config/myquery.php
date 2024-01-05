<?php

return [

    /*
    |--------------------------------------------------------------------------
    | mysampler
    |--------------------------------------------------------------------------
    |
    | The url to the mysampler json API
    |
    */
    'mysampler' => env('MYSAMPLER_URL', 'https://epicsweb.jlab.org/myquery/mysampler'),

    /*
    |--------------------------------------------------------------------------
    | mysampler
    |--------------------------------------------------------------------------
    |
    | The url to the myget json API
    |
    */
    'myget' => env('MYGET_URL', 'https://epicsweb.jlab.org/myquery/myget'),

    /*
    |--------------------------------------------------------------------------
    | deployment
    |--------------------------------------------------------------------------
    |
    | The mya deployment to query by default.
    | Valid values are 'ops' and 'history'.  Generally speaking use 'ops' for most recent six months and
    | 'history' for anything older.
    |
    */
    'deployment' => env('MYA_DEPLOYMENT','ops'),

    /*
     |--------------------------------------------------------------------------
     | strategy
     |--------------------------------------------------------------------------
     |
     |  The data fetching strategy to use by default.
     | 's' for streaming
     | 'n' for N-queries
     |
     */
    'strategy' => 's',

    /*
     |--------------------------------------------------------------------------
     | max_samples
     |--------------------------------------------------------------------------
     |
     |  Max samples to fetch in a single myquery
     |
     */
    'max_samples' => '10000',

];
