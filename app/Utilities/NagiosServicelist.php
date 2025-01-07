<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use Illuminate\Support\Collection;

class NagiosServicelist extends NagiosData
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

    public function services()
    {
        $services = new Collection;
        foreach ($this->getData()->data->servicelist as $host => $service) {
            $services->put($host, $service);
        }

        return $services;
    }

    public function filterByStatus($status)
    {
        return $this->services()->filter(function ($value, $key) use ($status) {
            foreach ($value as $serviceName => $detail) {
                if ($detail->status == $status) {
                    return true;
                }
            }

            return false;
        });
    }

    public function filterByNotStatus($status)
    {
        return $this->services()->filter(function ($value, $key) use ($status) {
            foreach ($value as $serviceName => $detail) {
                if ($detail->status != $status) {
                    return true;
                }
            }

            return false;
        });
    }

    public function countNotOk()
    {
        return $this->filterByNotStatus('ok')->count();
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     */
    public function query(): array
    {
        //?query=hostlist&formatoptions=whitespace+enumerate+bitmask+duration&hoststatus=up+down+unreachable';
        return [
            'query' => 'servicelist',
            'details' => 'true',
            'formatoptions' => 'whitespace enumerate bitmask duration',
            //'hoststatus' => 'up down unreachable',
        ];
    }
}
