<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 1/31/19
 * Time: 10:45 AM
 */

namespace App\Utilities;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

abstract class ClimateData implements ClimateDataContract
{
    const DEGREE_DAY_BASE = 65;

    const SOURCE_NAME = '';

    protected $date;

    protected $data;

    public function __construct()
    {
        $this->date = Carbon::yesterday();
        $this->date->hour(0)->minute(0)->second(0);
    }

    abstract protected function getApiData();

    abstract public function extractData($fetched);

    abstract public function highTemp();

    abstract public function lowTemp();

    public function setDate($date)
    {
        $this->date = Carbon::parse($date);
    }

    public function getDate()
    {
        return $this->date;
    }

    public function sourceName()
    {
        return static::SOURCE_NAME;
    }

    public function data()
    {
        if (empty($this->data)) {
            $this->fetchAndSetData();
        }

        return $this->data;
    }

    public function get($key)
    {
        if (isset($this->data()->$key)) {
            return $this->data()->$key;
        }
        throw new \Exception("Data key $key is not set");
    }

    public function avgTemp()
    {
        return ($this->highTemp() + $this->lowTemp()) / 2;
    }

    public function heatingDegreeDays()
    {
        if ($this->avgTemp() < self::DEGREE_DAY_BASE) {
            return self::DEGREE_DAY_BASE - $this->avgTemp();
        }

        return 0;
    }

    public function coolingDegreeDays()
    {
        if ($this->avgTemp() > self::DEGREE_DAY_BASE) {
            return $this->avgTemp() - self::DEGREE_DAY_BASE;
        }

        return 0;
    }

    protected function fetchAndSetData()
    {
        try {
            $fetched = $this->getApiData();
            $this->data = $this->extractData($fetched);
        } catch (\Exception $e) {
            Log::error($e);
            throw new ClimateDataException('Error retrieving climate data');
        }
    }
}
