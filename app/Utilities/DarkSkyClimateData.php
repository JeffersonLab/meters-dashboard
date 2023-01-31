<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 1/30/19
 * Time: 11:48 AM
 */

namespace App\Utilities;



use Illuminate\Support\Facades\Log;


class DarkSkyClimateData extends ClimateData
{

    const SOURCE_NAME = 'darksky';


    function highTemp(){
        return $this->get('temperatureHigh');
    }

    function lowTemp(){
        return $this->get('temperatureLow');
    }


    function extractData($fetched){
        if ($this->hasExpectedFormat($fetched)){
            $data = $fetched->daily->data[0];
            if ($data->time == $this->date->timestamp){
                return $data;
            }
        }
        return [];
    }

    protected function hasExpectedFormat($data){
        return is_object($data)
                && isset($data->daily)
                && isset($data->daily->data)
                && is_array($data->daily->data)
                && count($data->daily->data) == 1;
    }


    protected function getApiData(){
        $darkSky = app()->make('darksky');

        return $darkSky->location(env('LATITUDE'),env('LONGITUDE'))
            ->atTime($this->date->timestamp)
            ->excludes(['minutely','hourly', 'alerts', 'flags'])
            ->get();
    }

}