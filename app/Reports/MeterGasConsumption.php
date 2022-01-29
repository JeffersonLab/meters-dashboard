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
class MeterGasConsumption extends Consumption
{

    protected $title = 'Per-Meter Gas Consumption';
    protected $description = 'This report details natural gas consumption on a per-meter basis over a user-specified time interval';
    protected $pv = 'ccf';  // default
    protected $pvOptions = ['ccf'=>'CCF'];

    public function initItems()
    {
        $this->items = Meter::where('type', 'gas')->get();
        $this->itemType = 'meter';
    }


}
