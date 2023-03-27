<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/31/17
 * Time: 5:24 PM
 */

namespace App\Models\DataTables;

use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DataTableReporter
{
    use DateRangeTrait;

    /**
     * @var DataTableInterface
     */
    protected $model;

    protected $dataTableFk = 'meter_id';

    /**
     * DataTableReporter constructor.
     */
    public function __construct(DataTableInterface $meter)
    {
        $this->model = $meter;
        $this->defaultDates();
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($var)
    {
        switch ($var) {
            case 'begins_at': return $this->begins_at;
            case 'ends_at': return $this->ends_at;
        }
        throw new \Exception('property not available');
    }

    /**
     * Accepts a collection of label/value objects where the values are readings of
     * an accumulative variable (e.g. kWh) and returns a new
     * collection of label/value objects
     * where the values are the differences between successive items.
     */
    public function intervalDifferences($data): Collection
    {
        $previous = null;
        $dataSeries = new Collection();
        foreach ($data as $datum) {
            if ($previous !== null) {
                $dataSeries->push((object) [
                    'label' => $previous->label,
                    'value' => $this->odometerDifference($previous->value, $datum->value),
                ]);
            }
            $previous = $datum;
        }

        return $dataSeries;
    }

    /**
     * Accepts two sequential odometer readings $x, $y and returns their difference
     * accounting for the fact that the odometer may have "rolled over" in between.
     *
     * @param  int  $x first reading
     * @param  int  $y second reading
     * @param  int  $rollover odometer limit
     */
    public function odometerDifference(int $x, int $y, int $rollover = 1000000): int
    {
        // Can't do math on null values!
        if ($x === null || $y === null) {
            return null;
        }

        if ($x > $y) {
            // A meter transitioning from a positive value to exactly 0 seems
            // generally not to be the result of odometer rollover,
            // but a sign of something being reset and/or initialized
            // back to 0.  We will treat it as missing data and
            // return null.
            if ($y == 0) {
                return null;
            }

            return $rollover - $x + $y;
        }

        return $y - $x;
    }

    /**
     * Returns a collection containing one value per day of the current date range,
     * for the specified PV.
     *
     * The value is computed as the difference between the first and last values of
     * the PV property on the given day.
     */
    public function dailyPv($pv): Collection
    {
        $data = $this->dateRangeQuery()->get(['date', "$pv as value"]);

        return $this->dailyDifferences($data);
    }

    /**
     * Returns the available readings for a PV within the current date range.
     *
     *
     * @return Collection of {label, value} objects
     */
    public function pvReadings($pv): Collection
    {
        $data = $this->dateRangeQuery()->get(['date as label', "$pv as value"]);

        return $data;
    }

    /**
     * Query to obtain data for specified meter within current objects
     * begins_at and ends_at timestamps.
     */
    public function dateRangeQuery($direction = 'asc'): Builder
    {
        return $this->model->dataTable()
            ->where($this->dataTableFk, '=', $this->model->id)
            ->where('date', '>=', $this->begins_at)
            ->where('date', '<=', $this->ends_at)
            ->orderBy('date', $direction);
    }

    /**
     * Returns the earliest row of data in the date range query
     */
    public function firstData()
    {
        return $this->dateRangeQuery()->first();
    }

    /**
     * Returns latest row of data in the date range query
     */
    public function lastData()
    {
        return $this->dateRangeQuery('desc')->first();
    }

    /**
     * Returns a collection containing one value per day of the current date range.
     *
     * The value is computed as the difference between the first and last values of
     * the kWh property on the given day.
     *
     * @param  Collection  $data {date, value} objects
     * @return Collection of objects {label, value}
     */
    public function dailyDifferences(Collection $data): Collection
    {
        $begin = Carbon::create($this->begins_at->year, $this->begins_at->month, $this->begins_at->day);
        $end = Carbon::create($this->ends_at->year, $this->ends_at->month, $this->ends_at->day);

        $date = clone $begin;
        $dataSeries = new Collection();
        while ($date->timestamp <= $end->timestamp) {
            $label = $date->format('Y-m-d');
            $value = null;

            // First and last items of the current day
            $thisDay = $this->dataForDay($data, $date);
            $thisDayFirstItem = $thisDay->first();
            $thisDayLastItem = $thisDay->last();

            // First item of the following day
            $date->addDay();
            $nextDay = $this->dataForDay($data, $date);
            $nextDayFirstItem = $nextDay->first();

            // Obtain a difference between initial reading of current day and either:
            //   a) initial reading of following day if it begins at midnight
            //   b) final reading if the current day
            if ($thisDayFirstItem) {
                // @TODO Should probably annotate values for truncated time periods.
                if ($nextDayFirstItem && date('H:i', strtotime($nextDayFirstItem->date)) === '00:00') {
                    $value = $this->odometerDifference($thisDayFirstItem->value, $nextDayFirstItem->value);
                } else {
                    $value = $this->odometerDifference($thisDayFirstItem->value, $thisDayLastItem->value);
                }
            }

            if ($date->timestamp <= $end->timestamp) {
                $dataSeries->push(
                    (object) [
                        'label' => $label,
                        'value' => $value,
                    ]
                );
            }
        }

        return $dataSeries;
    }

    /**
     * Returns a collection of items from the source that fall on the
     * specified day.
     */
    public function dataForDay(Collection $collection, Carbon $date): Collection
    {
        return $collection->filter(function ($value) use ($date) {
            return date('Y-m-d', strtotime($value->date)) == $date->format('Y-m-d');
        });
    }

    /**
     * Returns the maximum value of the specified PV column within the current date range
     */
    public function maxPv(string $pv): Collection
    {
        return $this->dateRangeQuery()->max($pv);
    }

    /**
     * Accepts a collection of objects containing label and value properties and converts
     * returns a collection containing x and y where x is a javascript timestamp integer
     * and y is the value.
     */
    public function canvasTimeSeries(Collection $data): Collection
    {
        return $data->map(function ($item) {
            return (object) [
                'x' => strtotime($item->label) * 1000,
                'y' => $this->number($item->value),
            ];
        });
    }

    /**
     * Function to cast a string to an integer or a float
     * as most appropriate.
     *
     * @return float|int
     */
    public function number(string $val)
    {
        if ((int) $val === (float) $val) {
            return (int) $val;
        } else {
            return (float) round($val, 1);
        }
    }
}
