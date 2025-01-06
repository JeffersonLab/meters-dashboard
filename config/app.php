<?php

use Illuminate\Support\Facades\Facade;

return [

    'mix_url' => env('MIX_ASSET_URL', null),

    'aliases' => Facade::defaultAliases()->merge([
        // ...
    ])->toArray(),

];
