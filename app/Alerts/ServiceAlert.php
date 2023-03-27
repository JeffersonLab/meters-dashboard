<?php

namespace App\Alerts;

use App\Models\Meters\Meter;

/**
 * Class ServiceAlert
 *
 * Alerts obtained by Nagios Service Checks.
 */
class ServiceAlert implements AlertInterface
{
    protected $service;

    protected $meter;

    protected $type;

    public function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * Identifies the type of alert
     */
    public function type()
    {
        return $this->type;
    }

    public function description()
    {
        return $this->service->description;
    }

    public function message()
    {
        if ($this->service->long_plugin_output) {
            return $this->longMessage();
        }

        return $this->service->plugin_output;
    }

    public function longMessage()
    {
        return str_replace("\n", '<br />', $this->service->long_plugin_output);
    }

    public function status()
    {
        return $this->service->status;
    }

    public function isAcknowledged()
    {
        if ($this->service->problem_has_been_acknowledged) {
            return true;
        }

        return false;
    }

    public function lastCheck()
    {
        return $this->unixTime($this->service->last_check);
    }

    public function meter()
    {
        if (! $this->meter) {
            $this->meter = Meter::where('name', $this->service->host_name)->first();
        }

        return $this->meter;
    }

    /**
     * Converts nagios timestamps to unix integer timestamps.
     */
    public function unixTime(int $nagiosTime): int
    {
        return intval($nagiosTime / 1000);
    }
}
