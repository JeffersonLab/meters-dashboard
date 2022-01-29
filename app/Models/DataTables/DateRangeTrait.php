<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 2:34 PM
 */

namespace App\Models\DataTables;

use Carbon\Carbon;

trait DateRangeTrait
{

    protected $begins_at;  // earliest data of range
    protected $ends_at;    // latest data of range


    /**
     * Set default dates.
     */
    function defaultDates()
    {
        $this->defaultBeginning();
        $this->defaultEnding();
    }


    /**
     * Sets default beginning date for reporting
     *
     * Default: 00:00 on first day of current month.
     *
     */
    function defaultBeginning()
    {
        $date = Carbon::now();
        $date->day(1)->hour(0)->minute(0)->second(0);
        $this->beginning($date->format('Y-m-d H:i:s'));
    }



    /**
     * Chainable method to set the beginning of the reporting date range.
     * @param $date
     * @return static
     */
    function beginning($date)
    {
        $this->begins_at = new Carbon($date);
        return $this;
    }

    /**
     * Sets default ending date for reporting
     */
    function defaultEnding()
    {
        if (is_a($this->begins_at,Carbon::class)){
            // End at start of month that follows begins_at
            $date = clone $this->begins_at;
            $date->addMonth()->day(1);
        }else{
            $date = new Carbon('next month');
            $date->day(1);
        }
        if ($date->greaterThan(Carbon::today())){
            $date = Carbon::today();
        }

        $date->hour(0)->minute(0)->second(0);
        $this->ending($date->format('Y-m-d H:i:s'));
    }

    /**
     * Chainable method to set the ending of the reporting date range.
     * @param $date
     * @return static
     */
    function ending($date)
    {
        $this->ends_at = new Carbon($date);
        return $this;
    }




    function beginsAt($format=null){
        if ($format === null){
            if (0 == $this->begins_at->hour && 0 == $this->begins_at->minute){
                return $this->begins_at->format('Y-m-d');
            }else{
                return $this->begins_at->format('Y-m-d H:i');
            }

        }
        return $this->begins_at->format($format);
    }


    function endsAt($format=null){
        if ($format === null){
            if (0 == $this->ends_at->hour && 0 == $this->ends_at->minute){
                return $this->ends_at->format('Y-m-d');
            }else{
                return $this->ends_at->format('Y-m-d H:i');
            }

        }
        return $this->ends_at->format($format);
    }




}
