<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/31/17
 * Time: 5:24 PM
 */

namespace App\Models\DataTables;

class BuildingDataTableReporter extends DataTableReporter
{
    /*
     * The foreign key in building_data is building_id
     * and not the default of meter_id.
     */
    protected $dataTableFk = 'building_id';
}
