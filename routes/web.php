<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CoolingTowerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\PresenterGraphController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::match(['GET', 'POST'], '/login', [AuthController::class, 'login'])->name('login');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [BuildingController::class, 'siteMap'])->name('home');

Route::get('meters/{meter}', [MeterController::class, 'show'])->name('meters.show');

Route::get('meters', [MeterController::class, 'index'])->name('meters.index');

Route::get('monitor/{type}', [MeterController::class, 'monitor'])->name('monitor');

Route::get('presenter-graph', [PresenterGraphController::class, 'get'])->name('presenter_graph');
Route::get('presenter-graph/data', [PresenterGraphController::class, 'data'])->name('presenter_graph.data');

Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.item');

Route::get('reports/{report}/excel', [ReportController::class, 'excel'])->name('reports.excel');

Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');

Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

Route::get('buildings', [BuildingController::class, 'index'])->name('buildings.index');

Route::get('map', [BuildingController::class, 'siteMap'])->name('buildings.map');

Route::get('buildings/substation-summary', [BuildingController::class, 'substationSummary'])->name('buildings.substation_summary');

Route::get('buildings/{building}', [BuildingController::class, 'show'])->name('buildings.show');

Route::get('cooling-towers/{building}', [CoolingTowerController::class, 'show'])->name('cooling_towers.show');

Route::get('/home', [HomeController::class, 'index'])->name('home');
