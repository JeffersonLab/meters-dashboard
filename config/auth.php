<?php
return [

    'routes' => [
        'login' => env('LOGIN_ROUTE_NAME', 'sso.login'),
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', Jlab\Auth\User::class),
        ],
    ],
    'guards' => [
        'api' => [
            'driver' => 'jtoken',
            'provider' => 'users',
        ],
    ],

];
