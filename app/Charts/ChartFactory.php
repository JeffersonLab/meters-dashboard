<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/12/17
 * Time: 11:01 AM
 */

namespace App\Charts;

use App\Models\DataTables\DataTableInterface;
use Illuminate\Http\Request;

class ChartFactory
{
    /**
     * Returns an object that implements ChartInterface.
     *
     * @param  string  $name - identifies which chart to instantiate.
     * @param  DataTableInterface  $model - passed to the chart constructor
     * @param  Request  $request  - HTTP Request parameters
     * @return ChartInterface
     *
     * @throws \Exception
     */
    public static function make(string $name, DataTableInterface $model, Request $request): ChartInterface
    {
        switch (strtolower($name)) {
            case 'dailykwh': $chart = new DailyConsumption($model, 'totkWh', 'Daily KwH');
            break;
            case 'dailymbtu': $chart = new DailyConsumption($model, 'totMBTU', 'Daily MBTU');
            break;
            case 'dailygallons': $chart = new DailyConsumption($model, 'gal', 'Daily Gallons');
            break;
            case 'dailyccf': $chart = new DailyConsumption($model, 'ccf', 'Daily CCF');
            break;
            case 'readingskw': $chart = new MeterReadings($model, 'totkw', 'kW Readings');
            break;
            case 'readingsllvolt': $chart = new MeterReadings($model, 'llVolt', 'Voltage Readings');
            break;
            case 'readingsgpm': $chart = new MeterReadings($model, 'galPerMin', 'GPM Readings');
            break;
            case 'multimeter': /** @noinspection PhpParamsInspection */
                $chart = new MultiMeter($model);
                break;
            default: $chart = null;
        }

        if (! $chart instanceof ChartInterface) {
            abort(404, 'The requested chart name is not available');
        }
        $chart->applyRequest($request);

        return $chart;
    }
}
