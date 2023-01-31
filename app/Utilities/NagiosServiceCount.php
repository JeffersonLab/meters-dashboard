<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NagiosServiceCount extends NagiosData
{

    protected $cgi = 'statusjson.cgi';
    protected $lastResult;

    /**
     * Returns a collection of CED element objects
     *
     *
     * @return mixed
     * @internal param $name
     */
    function getData(){
        if (! $this->lastResult){
            $this->lastResult = $this->httpGet();
        }
        return $this->lastResult;
    }


    function ok(){
        return $this->getData()->data->count->ok;
    }

    function critical(){
        return $this->getData()->data->count->critical;
    }

    function warning(){
        return $this->getData()->data->count->warning;
    }

    function pending(){
        return $this->getData()->data->count->pending;
    }

    function unknown(){
        return $this->getData()->data->count->unknown;
    }

    function notOk(){
        return $this->critical() + $this->unknown() + $this->warning();
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    function query(){
        //?query=hostlist&formatoptions=whitespace+enumerate+bitmask+duration&hoststatus=up+down+unreachable';
        return [
            'query' => 'servicecount',
            'formatoptions' => 'whitespace enumerate bitmask duration',
        ];
    }






}