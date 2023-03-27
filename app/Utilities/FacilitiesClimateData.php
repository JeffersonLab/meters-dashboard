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

class FacilitiesClimateData extends ClimateData
{
    const SOURCE_NAME = 'jlab-weather';

    protected $webClient;

    public function __construct()
    {
        parent::__construct();
        $this->webClient = new Client([
            'base_uri' => env('https://www.jlab.org/fm/wx/VWS/data/daily/'),
            'verify' => false,
        ]);
    }

    public function highTemp()
    {
        return $this->get('temperatureHigh');
    }

    public function lowTemp()
    {
        return $this->get('temperatureLow');
    }

    public function extractData($fetched)
    {
        $data = new \StdClass();
        $data->temperatureHigh = max($fetched);
        $data->temperatureLow = min($fetched);

        return $data;
    }

    protected function getApiData()
    {
        $temps = [];
        $data = $this->httpGet()->getContents();
        foreach (explode("\n", $data) as $line) {
            if (preg_match('/^\d\d\:\d\d(am|pm)\s.*/', $line)) {
                $tokens = preg_split("/[\s]+/", $line);
                $temps[] = $tokens[4];
            }
        }

        return $temps;
    }

    /**
     * Performs data retrieval over http(s)
     *
     *
     * @throws \Exception
     */
    public function httpGet(): ?array
    {
        $response = $this->webClient->get($this->url(), [
            'timeout' => 3, // Response timeout
            'connect_timeout' => 3, // Connection timeout
        ]);

        if ($response->getStatusCode() == 200) {
            return $response->getBody();
        } else {
            Log::error($response->getBody());
            throw new \Exception('Web Data Retrieval Error '.$response->getStatusCode());
        }

        return null;
    }

    /**
     * Returns the filename pertinent to the date.
     *
     * Facilities weather station daily data is stored in text files of the format:
     */
    public function filename()
    {
        return $this->date->format('ymd').'.txt';
    }

    public function url()
    {
        return config('FACILITIES_WEATHER_DIR', 'https://www.jlab.org/fm/wx/VWS/data/daily/').$this->filename();
    }
}
