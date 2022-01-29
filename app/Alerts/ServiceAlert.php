<?php

namespace App\Alerts;


use App\Models\Meters\Meter;

/**
 * Class ServiceAlert
 *
 * Alerts obtained by Nagios Service Checks.
 *
 * @package App\Alerts
 */
class ServiceAlert implements AlertInterface
{


    protected $service;
    protected $meter;
    protected $type;


    function __construct($service)
    {
        $this->service = $service;
    }


    /**
     * Identifies the type of alert
     */
    function type(){
        return $this->type;
    }

    function description(){
        return $this->service->description;
    }

    function message(){
        if ($this->service->long_plugin_output){
	    return $this->longMessage();
        }
	return $this->service->plugin_output;
    }

    function longMessage(){
    	return str_replace("\n", "<br />", $this->service->long_plugin_output);
    }

    function status(){
        return $this->service->status;
    }

    function isAcknowledged(){
        if ($this->service->problem_has_been_acknowledged){
            return true;
        }
        return false;
    }

    function lastCheck(){
        return $this->unixTime($this->service->last_check);
    }

    function meter(){
        if (! $this->meter){
            $this->meter = Meter::where('name',$this->service->host_name)->first();
        }
        return $this->meter;
    }

    /**
     * Converts nagios timestamps to unix integer timestamps.
     *
     * @param int $nagiosTime
     * @return int
     */
    function unixTime($nagiosTime){
        return intval($nagiosTime/1000);
    }
}
