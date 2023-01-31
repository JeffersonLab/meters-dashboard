<?php

namespace App\Models\Meters;

class PowerMeter extends Meter
{

    public static $rules = [
        'name' => 'required | max:80',
        'type' => 'required | in:power',
        'epics_name' => 'max:40',
        'name_alias' => 'max:80',
    ];


}
