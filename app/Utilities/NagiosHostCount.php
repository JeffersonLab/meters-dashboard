<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use Illuminate\Support\Collection;

class NagiosHostCount extends NagiosData
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

    public function up()
    {
        return $this->getData()->data->count->up;
    }

    public function down()
    {
        return $this->getData()->data->count->down;
    }

    public function pending()
    {
        return $this->getData()->data->count->pending;
    }

    public function unreachable()
    {
        return $this->getData()->data->count->unreachable;
    }

    public function notUp()
    {
        return $this->down() + $this->unreachable();
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     */
    public function query(): array
    {
        //?query=hostlist&formatoptions=whitespace+enumerate+bitmask+duration&hoststatus=up+down+unreachable';
        return [
            'query' => 'hostcount',
            'formatoptions' => 'whitespace enumerate bitmask duration',
        ];
    }
}
