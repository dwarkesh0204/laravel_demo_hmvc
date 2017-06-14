<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['prefix' => 'beaconmanager','middleware' => 'other_db_connection'], function () {
    Route::resource("manufacture","ManufactureController");
    Route::post('manufacture/{id}/changeStatus',['as'=>'manufacture.changeStatus','uses'=>'ManufactureController@changeStatus']);
    Route::delete('manufacture/destory',['as'=>'manufacture.destroy','uses'=>'ManufactureController@destroy']);
    Route::get('data-table-manufacture', 'ManufactureController@manufactureData');

    Route::resource("iot","IotController");
    Route::delete('iot/destory',['as'=>'iot.destroy','uses'=>'IotController@destroy']);
    Route::get('data-table-iot', 'IotController@iotData');

    // Start Beacon Route
    Route::resource("beacon","BeaconController");
    Route::post('beacon/{id}/changeStatus',['as'=>'beacon.changeStatus','uses'=>'BeaconController@changeStatus']);
    Route::delete('beacon/destory',['as'=>'beacon.destroy','uses'=>'BeaconController@destroy']);
    Route::get('data-table-beacon', 'BeaconController@beaconData');
    // End Beacon Route
});

Route::group(['prefix' => 'beaconmanager'], function ()  {
    //Web service routes start
    Route::post('webservice', 'WebserviceController@index');
    //Web service routes ends

});