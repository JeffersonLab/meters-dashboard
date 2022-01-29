<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/24/17
 * Time: 2:21 PM
 */

namespace App\Utilities;

use App\Exceptions\WebClientException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MySamplerData implements DataFetchContract
{
    use MySamplerTrait;



    /**
     * MySamplerData constructor.
     *
     * If the number of steps to retrieve is not specified, it will be calculated
     * by figuring out how many samples of stepSize will fill the interval between begin
     * and now().
     *
     * @param string $begin    start date for sampling
     * @param mixed  $channels array or space-delimited string of channels to fetch
     * @param string $stepSize number of seconds in each sample
     * @param null   $numSteps number of samples to retrieve
     */
    function __construct($begin, $channels, $stepSize=null, $numSteps=null )
    {
        $this->initWebClient(env('MYSAMPLER_URL'),false);
        $this->begin = new Carbon($begin);
        $this->stepSize = $stepSize ? $stepSize : config('meters.data_interval',900);
        $this->channels = (is_array($channels) ? implode(' ', $channels) : $channels);
        $this->numSteps = $this->numSteps($numSteps);
        $this->deployment = env('MYA_DEPLOYMENT','ops');
    }



    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    function query(){
        return array(
            's' => $this->stepSize,
            'n' => $this->numSteps,
            'channels' => $this->channels,
            'b' => $this->begin->format('Y-m-d H:i'),
            'm' => $this->deployment,
        );
    }



    /**
     * Organizes the data returned by mySampler into a simpler format of:
     * [
     *  ['date1' => $date,
     *   'chan1' => $chan1,
     *   'chan2' => $chan2,
     *  ],
     *  ['date2' => $date,
     *   'chan1' => $chan1,
     *   'chan2' => $chan2,
     *  ],
     *]
     * @param array $data
     * @return array
     */
    function organize($data){
        $organized = array();
        foreach ($data->data as $item){
            $organizedItem = [];
            $organizedItem['date'] = $item->date;
            foreach ($item->values as $valueObj){
                foreach (get_object_vars($valueObj) as $key => $val){
                    if ($val == '<undefined>'){
                        $organizedItem[$key] = NULL;
                    }else{
                        $organizedItem[$key] = $val;
                    }
                }
            }
        $organized[] = $organizedItem;
        }
        return $organized;
    }





}
