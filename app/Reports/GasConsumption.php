<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;

/**
 * Class GasConsumption
 *
 * Report on gas consumption for a set of meters between two dates.
 */
class GasConsumption extends Consumption
{
    protected $title = 'Gas Consumption';

    protected $description = 'This report details gas consumption on a per-meter basis over a user-specified time interval';

    protected $pv = 'ccf';
}
