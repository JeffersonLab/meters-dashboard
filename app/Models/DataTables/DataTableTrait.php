<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/28/17
 * Time: 9:06 AM
 */

namespace App\Models\DataTables;

use App\Exceptions\DataTableException;
use App\Exceptions\MeterDataException;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait DataTableTrait
{
    /**
     * Column in the *_data table
     *
     * @var string
     */
    protected $dataTableFk;

    //----- Abstract methods which trait inheritors must implement ------------------------------------------------//

    /**
     * The primary key value of the model instance.
     * Theoretically a primary key value could be non-integer, but in our application
     * it won't be by convention.
     */
    abstract public function primaryKeyValue(): int;

    /**
     * The array of fields that can be appended to
     * epics_name to form pvs.
     */
    abstract public function pvFields(): array;

    //------ Trait implemented methods ----------------------------------------------------------------------------//

    /**
     * Return the name of the column in the data table that is the foreign key
     * back to the parent table. (ex: building_id, meter_id)
     */
    public function dataTableFk(): string
    {
        return $this->dataTableFk;
    }

    /**
     * Returns a Query Builder for the appropriate data table.
     *
     *
     * @throws \Exception if physical meters are of different types;
     */
    public function dataTable(): Builder
    {
        return DB::table($this->tableName());
    }

    /**
     * Answer whether the data table has any rows for the current object.
     */
    public function hasData(): bool
    {
        if ($this->dataTable()->where($this->dataTableFk(), $this->id)->limit(1)->first()) {
            return true;
        }

        return false;
    }

    /**
     * Return the datetime of expected next data table row
     */
    public function nextDataDate()
    {
        $latest = $this->lastDataDate();
        if ($latest) {
            return date('Y-m-d H:i',
                strtotime($latest->date) + config('meters.data_interval', 900));
        } else {
            return $this->begins_at->format('Y-m-d H:00');
        }
    }

    /**
     * Return the datetime of most recent data table row
     */
    public function lastDataDate($col = 'date')
    {
        return $this->dataTable()
            ->where($this->dataTableFk(), $this->id)
            ->latest($col)       // specify column else defaults to 'created_at'
            ->first();
    }

    /**
     * Returns the appropriate daily consumption query for the meter type.
     * #
     */
    public function dailyConsumptionQuery($field, Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->periodicConsumptionQuery($field, $beginDate, $endDate, 'daily');
    }

    public function dailyGasConsumptionQuery(Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->periodicConsumptionQuery('ccf', $beginDate, $endDate, 'daily');
    }

    public function dailyPowerConsumptionQuery(Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->periodicConsumptionQuery('totkWh', $beginDate, $endDate, 'daily');
    }

    public function dailyWaterConsumptionQuery(Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->periodicConsumptionQuery('gal', $beginDate, $endDate, 'daily');
    }

    public function hourlyGasConsumptionQuery(Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->periodicConsumptionQuery('ccf', $beginDate, $endDate, 'hourly');
    }

    public function hourlyPowerConsumptionQuery(Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->periodicConsumptionQuery('totkWh', $beginDate, $endDate, 'hourly');
    }

    public function hourlyWaterConsumptionQuery(Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->periodicConsumptionQuery('gal', $beginDate, $endDate, 'hourly');
    }

    /**
     * between two dates.
     *
     * @param  Carbon  $atDate
     *
     * @throws \Exception
     */
    public function dataBetweenQuery(Carbon $beginDate, Carbon $endDate): Builder
    {
        return $this->dataTable()
            ->select($this->dataColumns())
            ->where($this->dataTableFk(), $this->primaryKeyValue())
            ->whereBetween('date', [$beginDate, $endDate])
            ->orderBy('date');
    }

    protected function periodicConsumptionQuery($field, Carbon $beginDate, Carbon $endDate, $granularity = null): Builder
    {
        $this->assertHasField($field);
        /**
        The Query builder below constructs a function akin to the raw sql shown below.
        -- This first portion is fetched as a subquery
        with running_total as (
        select date, gal from water_meter_data_115
        where
        date between '2022-12-01' and '2023-01-01'
        and hour(date) = 0
        and minute(date) = 0
        )
        -- and this section portion pulls from that subquery
        select date, gal,
        lead(gal, 1) over (order by date) as  next_gal,
        lead(gal, 1) over (order by date) - gal as  consumed
        from running_total;
         */
        switch ($granularity) {
            case 'hourly': $subQuery = DB::table($this->hourlyDataBetweenQuery($beginDate, $endDate));
                break;
            case 'daily' : $subQuery = DB::table($this->dailyDataBetweenQuery($beginDate, $endDate));
                break;
            default:  $subQuery = DB::table($this->dataBetweenQuery($beginDate, $endDate));
        }
        /*
         * The lead() function is a mysql window function to efficiently gather periodic column
         * differences which equate to periodic consumption.
         */
        return DB::table($subQuery)
            ->select(['date', $field])
            ->selectRaw("lead($field, 1) over (order by date) as  next_val")
            ->selectRaw("lead($field, 1) over (order by date) - $field as  consumed");
    }

    public function hourlyDataBetweenQuery(Carbon $beginDate, Carbon $endDate)
    {
        return $this->dataBetweenQuery($beginDate, $endDate)
            ->whereRaw('minute(date) = 0');
    }

    public function dailyDataBetweenQuery(Carbon $beginDate, Carbon $endDate)
    {
        return $this->hourlyDataBetweenQuery($beginDate, $endDate)
            ->whereRaw('hour(date) = 0');
    }

    public function dataBetween(Carbon $beginDate, Carbon $endDate, $granularity = null)
    {
        switch ($granularity) {
            case 'hourly': return $this->hourlyDataBetweenQuery($beginDate, $endDate)->get();
            case 'daily' : return $this->dailyDataBetweenQuery($beginDate, $endDate)->get();
            default:  return $this->dataBetweenQuery($beginDate, $endDate)->get();
        }
    }

    /**
     * Columns to return for dataBetween queries
     *
     * @return array|string[]
     */
    public function dataColumns()
    {
        return array_merge(['id', 'date'], $this->fields());
    }

    public function hasField($field)
    {
        return in_array($field, $this->fields());
    }

    /**
     * The list of database fields.
     *
     * @return array|string[]
     */
    public function fields()
    {
        return array_map(function ($val) {
            // The database field name is the same as the PV field name with the
            // delimiter character removed.
            return ltrim($val, ':');
        }, $this->pvFields());
    }

    /**
     * Throw an exception if field is not a valid.
     *
     *
     * @throws MeterDataException
     */
    protected function assertHasField($field): bool
    {
        if (! $this->hasField($field)) {
            throw new DataTableException("{$field} is not a field of Meter {$this->primaryKeyValue()}");
        }

        return true;
    }
}
