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

abstract class CEDData implements DataFetchContract
{

    protected $webClient;

    function __construct()
    {
        $this->webClient = new Client(['base_uri'=>env('CED_URL')]);
    }

    /**
     * Returns a collection of CED element objects
     *
     * @return mixed
     * @internal param $name
     */
    abstract function getData();


    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    abstract function query();


    /**
     * @return mixed|null
     * @internal param array $query
     */
    function httpGet(){
        $response = $this->webClient->get('inventory',['query' => $this->query()]);
        if ($response->getStatusCode() == 200){
            $body = $response->getBody();
            return json_decode($body);
        }else{
            Log::error($response->getBody());
            throw new Exception('CED Retrieval Error '.$response->getStatusCode());
        }
    }


}