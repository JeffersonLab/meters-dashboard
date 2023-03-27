<?php

namespace App\Utilities;

use Carbon\Carbon;

class MySamplerStatsData
{
    use MySamplerTrait;

    /**
     * MySamplerStats constructor.
     *
     * If the number of steps to retrieve is not specified, it will be calculated
     * by figuring out how many samples of stepSize will fill the interval between begin
     * and now().
     *
     * @param  string  $begin    start date for sampling
     * @param  mixed  $channels array or space-delimited string of channels to fetch
     * @param  string  $stepSize number of seconds in each sample
     * @param  null  $numSteps number of samples to retrieve
     */
    public function __construct(string $begin, $channels, string $stepSize = null, $numSteps = null)
    {
        $this->initWebClient(env('MYSAMPLERSTATS_URL'), false);
        $this->begin = new Carbon($begin);
        $this->stepSize = $stepSize ? $stepSize : config('meters.data_interval', 900);
        $this->channels = (is_array($channels) ? implode(',', $channels) : $channels);
        $this->numSteps = ($numSteps ? $numSteps : $this->calcNumSteps());
        $this->deployment = env('MYA_DEPLOYMENT', 'ops');
    }

    /**
     * Returns the query parameters expected by mySampler
     * as an array.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            's' => $this->stepSize,
            'n' => $this->numSteps,
            'sUnit' => 'second',
            'l' => $this->channels,
            'b' => $this->begin->format('Y-m-d H:i'),
            'm' => $this->deployment,
        ];
    }

    /**
     * Organizes the data returned by myStatsSampler into a simpler format of:
     *
     * [
     *  ['date1' => $date,
     *   'chan1' => $chan1Stats,
     *   'chan2' => $chan2Stats,
     *  ],
     *  ['date2' => $date,
     *   'chan1' => $chan1Stats,
     *   'chan2' => $chan2Stats,
     *  ],
     *]
     *
     * @param  array  $data
     * @return array
     *
     * @throws \Exception
     */
    public function organize(array $data): array
    {
        $organized = [];
        foreach ($data->data as $item) {
            if (isset($item->error)) {
                throw new \Exception($item->error);
            }
            $organized[] = $item;
        }

        return $organized;
    }
}
