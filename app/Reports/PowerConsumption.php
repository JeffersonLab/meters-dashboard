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
class PowerConsumption extends Consumption
{

    protected $title = 'Power Consumption';
    protected $description = 'This report details electricity consumption on a per-meter basis over a user-specified time interval';
    protected $pv = 'totkWh';  // default
    protected $pvOptions = ['totkWh'=>'kWh','totMBTU'=>'MBTU'];

    public function initItems()
    {
        $this->items = Meter::where('type', 'power')->get();
        $this->itemType = 'meter';
    }

    /**
     * Returns the view that should be used to render the report.
     *
     */
    public function view(){
        return view('reports.consumption')
            ->with('report', $this);
    }
}
