<?php

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

Route::match(['GET', 'POST'], '/login', [
    'as' => 'login',
    'uses' => 'AuthController@login',
]);

Route::get('/logout', 'AuthController@logout')->name('logout');

Route::get('/', 'BuildingController@siteMap')->name('home');

Route::get('meters/{meter}', 'MeterController@show')->name('meters.show');

Route::get('meters', 'MeterController@index')->name('meters.index');

Route::get('monitor/{type}', 'MeterController@monitor')->name('monitor');

Route::get('reports/{report}', 'ReportController@show')->name('reports.item');

Route::get('reports/{report}/excel', 'ReportController@excel')->name('reports.excel');

Route::get('alerts', 'AlertController@index')->name('alerts.index');

Route::get('reports', 'ReportController@index')->name('reports.index');

Route::get('buildings', 'BuildingController@index')->name('buildings.index');

Route::get('map', 'BuildingController@siteMap')->name('buildings.map');

Route::get('buildings/substation-summary', 'BuildingController@substationSummary')->name('buildings.substation_summary');

Route::get('buildings/{building}', 'BuildingController@show')->name('buildings.show');

Route::get('cooling-towers/{building}', 'CoolingTowerController@show')->name('cooling_towers.show');

Route::get('/test', function () {
//    dd(file_get_contents('http://epics2web:8080/epics2web/caget?pv=87-L1%3AllVolt'));
    return view('Test');

//    $c = new \App\Utilities\FacilitiesClimateData();
//    $c->setDate('2019-01-29');
//    var_dump($c->heatingDegreeDays());
//    var_dump($c->coolingDegreeDays());
//    dd($c->data());

//    $vm = new \App\Meters\VirtualMeter();
//    $vm->setMeters(Meter::whereIn('id',[49,50])->get());
//    $chart = new \App\Charts\MultiMeter($vm);
//
//    dd($chart->toArray());
});

Route::get('/home', 'HomeController@index')->name('home');
