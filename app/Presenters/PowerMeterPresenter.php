<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/27/17
 * Time: 10:03 AM
 */

namespace App\Presenters;

class PowerMeterPresenter extends MeterPresenter
{
    public function defaultChart(): string
    {
        return 'dailykwh';
    }

    public function voltageParameters()
    {
        switch ($this->epics_name) {
            case '33MVA':
            case '40MVA':
                return $this->substationVoltageParameters();
            default:
                return $this->standard480Parameters();
        }
    }

    protected function substationVoltageParameters()
    {
        return (object) [
            'min' => 12200,
            'low' => null,
            'lolo' => 12400,
            'nominal' => 12600,
            'high' => null,
            'hihi' => 12800,
            'max' => 13000,
        ];
    }

    protected function standard480Parameters()
    {
        // low and high are ~3% deviation (15V)from nominal 480
        // lolo and hihi are 5% deviation (24V) from nominal 480
        return (object) [
            'min' => 440,
            'low' => 465,
            'lolo' => 456,
            'nominal' => 480,
            'high' => 495,
            'hihi' => 504,
            'max' => 520,
        ];
    }
}
