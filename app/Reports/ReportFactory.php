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
    /**

     */
    static function make($name, Request $request){

        switch ($name){
            case 'power-consumption' : $report = new PowerConsumption(); break;
            case 'meter-power-consumption' : $report = new MeterPowerConsumption(); break;
            case 'building-power-consumption' : $report = new BuildingPowerConsumption(); break;
            case 'meter-water-consumption' : $report = new MeterWaterConsumption(); break;
            case 'building-water-consumption' : $report = new BuildingWaterConsumption(); break;
            case 'meter-gas-consumption' : $report = new MeterGasConsumption(); break;
            case 'goal-buildings' : $report = new GoalBuildings(); break;
            case 'climate-data' : $report = new ClimateData(); break;
            case 'multi-meter' : $report = new MultiMeter(); break;
            case 'mya-stats' : $report = new MyaStats(); break;
            default: $report = null;
        }
        if (! $report){
            app()->abort('404', "The requested report is not available");
        }
        $report->applyRequest($request);
        return $report;
    }
}
