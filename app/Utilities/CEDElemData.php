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

class CEDElemData extends CEDData
{

    public $elem;

    function __construct($elem = null)
    {
        parent::__construct();
        $this->elem = $elem;
    }

    /**
     * Returns a collection of CED element objects
     *
     * @return mixed
     * @internal param $name
     */
    function getData(){
        $data = $this->httpGet();
        return $data->Inventory->elements[0];
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    function query(){
        return array(
            'wrkspc' => $this->workspace,
            'e' => $this->elem,
            'out' => 'json',
        );
    }



}
