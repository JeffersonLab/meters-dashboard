<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;

/**
 * Class WaterConsumption
 *
 * Report on water consumption for a set of meters between two dates.
 */
class WaterConsumption extends Consumption
{
    protected $title = 'Water Consumption';

    protected $description = 'This report details water consumption on a per-meter basis over a user-specified time interval';

    protected $pv = 'gal';
}
