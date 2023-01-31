<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use Illuminate\Support\Collection;

class NagiosServiceCount extends NagiosData
{
    protected $cgi = 'statusjson.cgi';

    protected $lastResult;

    /**
     * Returns a collection of CED element objects
     *
     *
     * @return mixed
     *
     * @internal param $name
     */
    public function getData()
    {
        if (! $this->lastResult) {
            $this->lastResult = $this->httpGet();
        }

        return $this->lastResult;
    }

    public function ok()
    {
        return $this->getData()->data->count->ok;
    }

    public function critical()
    {
        return $this->getData()->data->count->critical;
    }

    public function warning()
    {
        return $this->getData()->data->count->warning;
    }

    public function pending()
    {
        return $this->getData()->data->count->pending;
    }

    public function unknown()
    {
        return $this->getData()->data->count->unknown;
    }

    public function notOk()
    {
        return $this->critical() + $this->unknown() + $this->warning();
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    public function query()
    {
        //?query=hostlist&formatoptions=whitespace+enumerate+bitmask+duration&hoststatus=up+down+unreachable';
        return [
            'query' => 'servicecount',
            'formatoptions' => 'whitespace enumerate bitmask duration',
        ];
    }
}
