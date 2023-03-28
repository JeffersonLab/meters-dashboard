<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/12/17
 * Time: 11:01 AM
 */

namespace App\Reports;

use Illuminate\Http\Request;

class ReportFactory
{
    public static function make($name, Request $request)
    {
        switch ($name) {
            case 'power-consumption' : $report = new PowerConsumption();
                break;
            case 'water-consumption' : $report = new WaterConsumption();
                break;
            case 'gas-consumption' : $report = new GasConsumption();
                break;
            case 'cooling-tower-consumption' : $report = new CoolingTowerConsumption();
                break;
            case 'multi-meter' : $report = new MultiMeter();
                break;
            case 'mya-stats' : $report = new MyaStats();
                break;
            default: $report = null;
        }
        if (! $report) {
            app()->abort('404', 'The requested report is not available');
        }
        $report->applyRequest($request);

        return $report;
    }
}
