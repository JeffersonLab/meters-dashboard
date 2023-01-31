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

class CEDTypeData extends CEDData
{

    public $type;

    function __construct($type = null)
    {
        parent::__construct();
        $this->type = $type;
    }

    /**
     * Returns a collection of CED element objects
     *
     * @return mixed
     * @internal param $name
     */
    function getData(){
        $data = $this->httpGet();
        $collection = new Collection($data->Inventory->elements);
        return $collection;
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    function query(){
        return [
            'wrkspc' => $this->workspace,
            't' => $this->type,
            'out' => 'json',
        ];
    }



}
