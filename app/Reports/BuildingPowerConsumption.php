<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;



use App\Models\Buildings\Building;

/**
 * Class Consumption
 *
 * Report on resource consumption for a set of meters between two dates.
 *
 * @package App\Reports
 */
class BuildingPowerConsumption extends Consumption
{

    protected $title = 'Per-Building Power Consumption';
    protected $description = 'This report details electricity consumption on a per-building basis over a user-specified time interval';
    protected $pv = 'totkWh';  // default
    protected $pvOptions = ['totkWh'=>'kWh','totMBTU'=>'MBTU'];

    public function initItems()
    {
        $this->items = Building::all();
        $this->itemType = 'building';
    }


}
