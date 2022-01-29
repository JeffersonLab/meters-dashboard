<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;



use App\Models\Meters\Meter;

/**
 * Class Consumption
 *
 * Report on resource consumption for a set of meters between two dates.
 *
 * @package App\Reports
 */
class MeterWaterConsumption extends Consumption
{

    protected $title = 'Per-Meter Water Consumption';
    protected $description = 'This report details water consumption on a per-meter basis over a user-specified time interval';
    protected $pv = 'gal';  // default
    protected $pvOptions = ['gal'=>'Gallons'];

    public function initItems()
    {
        $this->items = Meter::where('type', 'water')->get();
        $this->itemType = 'meter';
    }


}
