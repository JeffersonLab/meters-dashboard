<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WeatherUndergroundData implements DataFetchContract
{

    protected $webClient;
    protected $lastResult;

    function __construct()
    {
        $this->webClient = new Client([
            'base_uri'=>env('WUNDERGROUND_URL'),
            'verify' => false
        ]);
    }

    /**
     * Returns a collection of CED element objects
     *
     * @return mixed
     * @internal param $name
     */
    function getData()
    {
        if (! $this->lastResult){
            $this->lastResult = $this->httpGet();
        }
        return $this->lastResult;
    }

    function history(){
        if (array_key_exists('history', get_object_vars($this->getData()))){
            return $this->getData()->history;
        }
        return null;
    }

    function dailySummary(){
        if (array_key_exists('dailysummary', get_object_vars($this->history()))){
            return $this->history()->dailysummary[0];   //API returns an array
        }
    }


    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    function query(){
        return [];  // The base URL suffices - for now.
    }


    /**
     * Performs data retrieval over http(s)
     * @return array|null
     * @throws \Exception
     */
    function httpGet(){
        try {
            $response = $this->webClient->get('yesterday/q/KPHF.json', [
                'query' => $this->query(),
		'proxy' => env('HTTP_PROXY', '')
            ]);
            if ($response->getStatusCode() == 200) {
                $body = $response->getBody();
                return json_decode($body);
            } else {
                Log::error($response->getBody());
                throw new \Exception('Weather Underground Retrieval Error ' . $response->getStatusCode());
            }
        }catch (Exception $e){
            Log::error($e);
            throw new \Exception($e->getMessage());
        }
        return null;
    }


}
