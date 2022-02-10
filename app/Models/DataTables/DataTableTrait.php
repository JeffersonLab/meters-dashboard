<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/28/17
 * Time: 9:06 AM
 */

namespace App\Models\DataTables;


use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait DataTableTrait
{

    // Column in the *_data table
    protected $dataTableFk;


    /**
     * Return the name of the column in the data table that is the foreign key
     * back to the parent table. (ex: building_id, meter_id)
     * @return string
     */
    public function dataTableFk(){
        return $this->dataTableFk;
    }

    /**
     * Returns a Query Builder for the appropriate data table.
     *
     * @return Builder
     * @throws \Exception if physical meters are of different types;
     */
    public function dataTable(): Builder
    {
        return DB::table($this->tableName());
    }

    /**
     * Answer whether the data table has any rows for the current object.
     *
     * @return bool
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
            return (date('Y-m-d H:i',
                strtotime($latest->date) + config('meters.data_interval', 900))
            );
        } else {
            return $this->begins_at->format('Y-m-d H:00');
        }
    }

    /**
     * Return the datetime of most recent data table row
     */
    public function lastDataDate()
    {
        return $this->dataTable()
            ->where($this->dataTableFk(), $this->id)
            ->latest()->first();

    }


}
