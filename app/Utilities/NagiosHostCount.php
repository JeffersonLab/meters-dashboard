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

class NagiosHostCount extends NagiosData
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


    function up(){
        return $this->getData()->data->count->up;
    }

    function down(){
        return $this->getData()->data->count->down;
    }

    function pending(){
        return $this->getData()->data->count->pending;
    }

    function unreachable(){
        return $this->getData()->data->count->unreachable;
    }

    function notUp(){
        return $this->down() + $this->unreachable();
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    function query(){
        //?query=hostlist&formatoptions=whitespace+enumerate+bitmask+duration&hoststatus=up+down+unreachable';
        return array(
            'query' => 'hostcount',
            'formatoptions' => 'whitespace enumerate bitmask duration',
        );
    }






}