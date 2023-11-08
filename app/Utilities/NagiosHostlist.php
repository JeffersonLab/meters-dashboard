<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use Illuminate\Support\Collection;

class NagiosHostlist extends NagiosData
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

    public function hosts()
    {
        $hosts = new Collection();
        foreach ($this->getData()->data->hostlist as $host) {
            $hosts->push($host);
        }

        return $hosts;
    }

    public function countNotUp()
    {
        return $this->hosts()->where('status', '!=', 'up')->count();
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     */
    public function query(): array
    {
        //?query=hostlist&formatoptions=whitespace+enumerate+bitmask+duration&hoststatus=up+down+unreachable';
        return [
            'query' => 'hostlist',
            'details' => 'true',
            'formatoptions' => 'whitespace enumerate bitmask duration',
            //'hoststatus' => 'up down unreachable',
        ];
    }
}
