<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/meters/chart-data',[
    'as' => 'meters.chart_data',
    'uses' => 'ApiController@meterChartData'
]);

Route::get('/buildings/chart-data',[
    'as' => 'buildings.chart_data',
    'uses' => 'ApiController@buildingChartData'
]);

Route::get('/reports/chart-data',[
    'as' => 'reports.chart_data',
    'uses' => 'ApiController@reportChartData'
]);
