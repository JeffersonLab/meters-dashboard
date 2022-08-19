<?php
return [

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    |
    | Here you can specify the base URL to be used when querying the CED web API.
    | IMPORTANT:  the URL must end with a slash /.
    |
    */
    'url' => env('CED_UR','https://ced.acc.jlab.org/'),


    /*
    |--------------------------------------------------------------------------
    | Workspace
    |--------------------------------------------------------------------------
    |
    | Here you can specify the workspace to use when fetching meter and building
    | data from CED.
    |
    */
    'workspace' => env('CED_WORKSPACE','OPS'),

];
