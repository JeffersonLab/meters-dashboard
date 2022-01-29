<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/9/17
 * Time: 2:22 PM
 */

namespace App\Models\DataTables;

/**
 * Interface DataTableInterface
 *
 * Contract that must be implemented for objects passed to a DataTableReporter.
 *
 * @package App\Meters
 */
interface DataTableInterface
{

    /**
     * @return DataTableReporter
     */
    function reporter();

    /**
     * Query to obtain the Query Builder for the model.
     *
     * The builder must reference a table with a column named 'date'.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    function dataTable();

    /**
     * Answer whether the object has any data in its data table
     */
    public function hasData();

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



}
