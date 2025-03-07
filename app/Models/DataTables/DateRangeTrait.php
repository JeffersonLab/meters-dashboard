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
    public function defaultDates()
    {
        $this->defaultBeginning();
        $this->defaultEnding();
    }

    /**
     * Sets default beginning date for reporting
     *
     * Default: 00:00 on first day of current month.
     */
    public function defaultBeginning()
    {
        $date = Carbon::now();
        $date->day(1)->startOfDay();
        $this->beginning($date->format('Y-m-d H:i:s'));
    }

    /**
     * Chainable method to set the beginning of the reporting date range.
     */
    public function beginning(string $date): static
    {
        $this->begins_at = Carbon::parse($date);

        return $this;
    }

    /**
     * Sets default ending date for reporting
     */
    public function defaultEnding()
    {
        if (is_a($this->begins_at, Carbon::class)) {
            // End at start of month that follows begins_at
            $date = clone $this->begins_at;
            $date->addMonth()->day(1)->startOfDay();
        } else {
            // End of the current month
            $date = new Carbon('next month');
            $date->day(1)->startOfDay();
        }
        if ($date->greaterThan(Carbon::today())) {
            $date = Carbon::tomorrow()->startOfDay();
        }

        $this->ending($date->format('Y-m-d H:i:s'));
    }

    /**
     * Chainable method to set the ending of the reporting date range.
     */
    public function ending(string $date): static
    {
        $this->ends_at = Carbon::parse($date);

        return $this;
    }

    public function beginsAt(?string $format = null): string
    {
        if ($format === null) {
            if ($this->begins_at->hour == 0 && $this->begins_at->minute == 0) {
                return $this->begins_at->format('Y-m-d');
            } else {
                return $this->begins_at->format('Y-m-d H:i');
            }
        }

        return $this->begins_at->format($format);
    }

    public function endsAt(?string $format = null): string
    {
        if ($format === null) {
            if ($this->ends_at->hour == 0 && $this->ends_at->minute == 0) {
                return $this->ends_at->format('Y-m-d');
            } else {
                return $this->ends_at->format('Y-m-d H:i');
            }
        }

        return $this->ends_at->format($format);
    }
}
