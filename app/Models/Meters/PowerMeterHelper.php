<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/13/18
 * Time: 4:16 PM
 */

namespace App\Models\Meters;

class PowerMeterHelper extends MeterHelper
{
    public function alarmLimits()
    {
        if (in_array($this->meter->epics_name, config('meters.substation', []))) {
            return $this->substationAlarmLimits();
        }

        return $this->standard480AlarmLimits();
    }

    protected function substationAlarmLimits()
    {
        //TODO fetch from "33MVA:llVolt.HIHI", etc.
        return (object) [
            'low' => 0,         //EPICS convention for not set
            'lolo' => 12200,
            'high' => 0,        //EPICS convention for not set
            'hihi' => 12850,
        ];
    }

    protected function standard480AlarmLimits()
    {
        // low and high are ~3% deviation (15V)from nominal 480
        // lolo and hihi are 5% deviation (24V) from nominal 480
        return (object) [
            'lolo' => 456,
            'low' => 465,
            'high' => 495,
            'hihi' => 504,
        ];
    }
}
