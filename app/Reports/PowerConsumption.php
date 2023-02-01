<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;

/**
 * Class PowerConsumption
 *
 * Report on power consumption for a set of meters between two dates.
 */
class PowerConsumption extends Consumption
{
    protected $title = 'Power Consumption';

    protected $description = 'This report details electricity consumption on a per-meter basis over a user-specified time interval';

    protected $pv = 'totkWh';
}
