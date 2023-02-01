<?php

use App\Http\Controllers\ApiController;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/meters/chart-data', [ApiController::class, 'meterChartData'])->name('meters.chart_data');

Route::get('/meters/table-data', [ApiController::class, 'meterTableData'])->name('meters.table_data');

Route::get('/buildings/chart-data', [ApiController::class, 'buildingChartData'])->name('buildings.chart_data');

Route::get('/reports/chart-data', [ApiController::class, 'reportChartData'])->name('reports.chart_data');
