<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/1/18
 * Time: 10:57 AM
 */

namespace App\Alerts;

use App\Models\Meters\Meter;

/**
 * Class MeterAlert
 *
 * Meter-specific alerts that don't arise from simple EPICS value checks.
 * An example could be an alert that the number of gallons recorded on a water
 * meter during the preceding 24 hours was too high or too low.
 */
class MeterAlert implements AlertInterface
{
    protected $meter;

    public $status;  // warning, critical, unknown

    public $description;

    public $message;

    protected $lastCheck;

    public function __construct(Meter $meter, $status)
    {
        $this->meter = $meter;
        $this->status = $status;
        $this->lastCheck = time();
    }

    /**
     * Identifies the type of alert
     */
    public function type()
    {
        return $this->meter->type;
    }

    public function description()
    {
        return $this->description;
    }

    public function message()
    {
        return $this->message;
    }

    public function status()
    {
        return $this->status;
    }

    public function isAcknowledged()
    {
        return false;
    }

    public function lastCheck()
    {
        return $this->lastCheck;
    }

    public function meter()
    {
        return $this->meter;
    }
}
