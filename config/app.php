<?php

use Illuminate\Support\Facades\Facade;

return [

    'proxy_url' => env('PROXY_URL', ''),

    'proxy_ip' => env('PROXY_IP', ''),

    'mix_url' => env('MIX_ASSET_URL', null),

    'aliases' => Facade::defaultAliases()->merge([
        // ...
    ])->toArray(),

];
