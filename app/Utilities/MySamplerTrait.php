<?php


namespace App\Utilities;


use App\Exceptions\WebClientException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Functionality common to classes that interact with myaweb json data sources.
 *
 * @package App\Utilities
 */
trait MySamplerTrait
{
    protected $webClient;

    protected $begin;
    protected $channels;
    protected $stepSize;
    protected $numSteps;
    protected $deployment;


    /**
     * Return query parameters to be used by webClient when
     * retrieving data.
     *
     * @return array
     */
    abstract function query();


    /**
     * (re)organize the array returned by Mya utils as may be
     * necessary for the implementing class.
     *
     * For example, perhaps convert from time-index to channel-index
     * array.
     *
     *
     * @param array $data
     * @return array
     */
    abstract function organize($data);


    /**
     * Initialize a webClient that will interact with web-based
     * Mya utlitiles.
     *
     * @param string $baseUri
     * @param bool $verifyCerts
     * @return void
     */
    function initWebClient($baseUri, $verifyCerts = true){
        $this->webClient = new Client([
            'base_uri'=>$baseUri,
            'verify' => $verifyCerts
        ]);
    }


    /**
     * Use stepSize to compute the number of steps to bridge
     * the time span between the specified begins date and now.
     *
     * @return int
     */
    function calcNumSteps(){
        $seconds = Carbon::now()->diffInSeconds($this->begin);
        return (int) floor($seconds/$this->stepSize);
    }

    /**
     * Choose the smaller value between calculated number of steps
     * and the max allowed number of steps.
     * The calculated value prevents requesting steps in the future.
     * The max value prevents asking for too many datapoints from the server.
     */
    function numSteps($numSteps = null){
        $desired = $numSteps ? $numSteps : $this->calcNumSteps();
        $max = config('meters.max_steps',5000);
        return ($desired > $max ) ? $max : $desired;
    }


    /**
     * Returns a collection of data retrieved from mySampler organized
     * by channel.
     *
     * @return Collection
     * @throws WebClientException
     */
    function getData(){
        $data = $this->httpGet($this->query());
        return new Collection($this->organize($data));
    }

    /**
     * Executes the query over HTTP
     *
     * @param array $query query parameters to use.
     * @return mixed|null
     * @throws WebClientException
     */
    protected function httpGet(array $query){
        $response = $this->webClient->get('data',['query' => $query]);
        if ($response->getStatusCode() == 200){
            $body = $response->getBody();
            return json_decode($body);
        }else{
            Log::error($response->getBody());
            throw new WebClientException('HTTP Retrieval Error '.$response->getStatusCode());
        }
    }
}
