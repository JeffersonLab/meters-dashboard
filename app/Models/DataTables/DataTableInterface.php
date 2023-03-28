<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/9/17
 * Time: 2:22 PM
 */

namespace App\Models\DataTables;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

/**
 * Interface DataTableInterface
 *
 * Contract that must be implemented for objects passed to a DataTableReporter.
 */
interface DataTableInterface
{
    public function reporter(): DataTableReporter;

    /**
     * The name of the table where meter data points are stored.
     */
    public function tableName(): string;

    /**
     * Obtain a Query Builder for the model.
     *
     * Note: The builder must reference a table with a column named 'date'.
     */
    public function dataTable(): Builder;

    /**
     * Answer whether the object has any data in its data table
     */
    public function hasData(): bool;

    /**
     * Put data into the object's data table.
     *
     * @return mixed
     */
    public function fillDataTable();

    /**
     * Return the datetime of expected next data table row
     */
    public function nextDataDate();

    /**
     * Return the datetime of most recent data table row
     */
    public function lastDataDate();

    /**
     * Returns the array of fields that can be appended to
     * epics_name to form pvs.
     */
    public function pvFields(): array;

    /**
     * Returns the array of fields to be used when creating database columns
     * for data storage.
     */
    public function dbFields(): array;

    public function dailyConsumptionQuery($field, Carbon $beginDate, Carbon $endDate): Builder;
}
