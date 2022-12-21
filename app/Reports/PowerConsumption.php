<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;



use App\Models\Meters\Meter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class PowerConsumption
 *
 * Report on power consumption for a set of meters between two dates.
 *
 * @package App\Reports
 */
class PowerConsumption extends Consumption
{

    protected $title = 'Power Consumption';
    protected $description = 'This report details electricity consumption on a per-meter basis over a user-specified time interval';
    protected $pv = 'totkWh';

}
