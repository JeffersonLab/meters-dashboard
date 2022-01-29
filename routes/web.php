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


Route::get('/',[
    'as' => 'home',
    'uses' => 'BuildingController@index'
]);


Route::get('/meters/{meter}',[
    'as' => 'meters.show',
    'uses' => 'MeterController@show'
]);

Route::get('/meters',[
    'as' => 'meters.index',
    'uses' => 'MeterController@index'
]);

Route::get('/monitor/{type}',[
    'as' => 'monitor',
    'uses' => 'MeterController@monitor'
]);

Route::get('/reports/{report}',[
    'as' => 'reports.item',
    'uses' => 'ReportController@show'
]);

Route::get('/reports/{report}/excel',[
    'as' => 'reports.excel',
    'uses' => 'ReportController@excel'
]);

Route::get('/alerts',[
    'as' => 'alerts.index',
    'uses' => 'AlertController@index'
]);

Route::get('/reports',[
    'as' => 'reports.index',
    'uses' => 'ReportController@index'
]);


Route::get('/buildings',[
    'as' => 'buildings.index',
    'uses' => 'BuildingController@index'
]);

Route::get('/buildings/{building}',[
    'as' => 'buildings.show',
    'uses' => 'BuildingController@show'
]);

//Route::get('/test', function () {
//
//
////    $c = new \App\Utilities\FacilitiesClimateData();
////    $c->setDate('2019-01-29');
////    var_dump($c->heatingDegreeDays());
////    var_dump($c->coolingDegreeDays());
////    dd($c->data());
//
//
////    $vm = new \App\Meters\VirtualMeter();
////    $vm->setMeters(Meter::whereIn('id',[49,50])->get());
////    $chart = new \App\Charts\MultiMeter($vm);
////
////    dd($chart->toArray());
//
//});


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

