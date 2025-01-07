<?php

return [

    'guards' => [
        'web' => [
            'driver' => 'jsession',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jtoken',
            'provider' => 'users',
        ],
    ],

];
