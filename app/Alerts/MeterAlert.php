<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/1/18
 * Time: 10:57 AM
 */

namespace App\Alerts;


use App\Meters\Meter;

/**
 * Class MeterAlert
 *
 * Meter-specific alerts that don't arise from simple EPICS value checks.
 * An example could be an alert that the number of gallons recorded on a water
 * meter during the preceding 24 hours was too high or too low.
 *
 * @package App\Alerts
 */
class MeterAlert implements AlertInterface
{

    protected $meter;
    protected $status;
    public $description;
    public $message;
    protected $lastCheck;



    function __construct(Meter $meter, $status)
    {
        $this->meter = $meter;
        $this->status = $status;
        $this->lastCheck = time();
    }

    /**
     * Identifies the type of alert
     */
    function type(){
        return $this->meter->type;
    }

    function description(){
        return $this->description;
    }

    function message(){
        return $this->message;
    }

    function status(){
        return $this->status;
    }

    function isAcknowledged(){
        return false;
    }

    function lastCheck(){
        return $this->lastCheck;
    }

    function meter(){
        return $this->meter;
    }

}