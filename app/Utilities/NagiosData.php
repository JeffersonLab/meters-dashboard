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

abstract class NagiosData implements DataFetchContract
{
    protected $webClient;

    protected $cgi;

    public function __construct()
    {
        $this->webClient = new Client([
            'base_uri' => env('NAGIOS_URL'),
            'verify' => false,
        ]);
    }

    /**
     * Returns a collection of CED element objects
     *
     * @return mixed
     *
     * @internal param $name
     */
    abstract public function getData();

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    abstract public function query();

    /**
     * Returns the nagios json cgi script to use
     *
     * @return mixed
     */
    public function cgi()
    {
        return $this->cgi;
    }

    /**
     * Converts nagios timestamps to unix integer timestamps.
     *
     * @param  int  $nagiosTime
     * @return int
     */
    public function unixTime($nagiosTime)
    {
        return intval($nagiosTime / 1000);
    }

    public function lastUpdated()
    {
        return $this->unixTime($this->getData()->result->last_data_update);
    }

    /**
     * Performs data retrieval over http(s)
     *
     * @return array|null
     *
     * @throws \Exception
     */
    public function httpGet()
    {
        try {
            $response = $this->webClient->get($this->cgi(), [
                'query' => $this->query(),
                'auth' => [env('NAGIOS_USERNAME'), env('NAGIOS_PASSWORD')],
                'timeout' => 3, // Response timeout
                'connect_timeout' => 3, // Connection timeout
            ]);
            if ($response->getStatusCode() == 200) {
                $body = $response->getBody();

                return json_decode($body);
            } else {
                Log::error($response->getBody());
                throw new \Exception('Nagios Retrieval Error '.$response->getStatusCode());
            }
        } catch (Exception $e) {
            Log::error($e);
            throw new \Exception($e->getMessage());
        }

        return null;
    }
}
