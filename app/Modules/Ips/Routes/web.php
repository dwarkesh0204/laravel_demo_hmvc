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

Route::group(['prefix' => 'ips','middleware' => 'other_db_connection'], function () {
    Route::resource("floorplan","FloorplanController");
    Route::delete('floorplan',['as'=>'floorplan.destroy','uses'=>'FloorplanController@destroy']);

    // Floorplan AJAX Routes //
    Route::post('/floorplan/ajaxExport', 'FloorplanController@ajaxExport');
    Route::post('/floorplan/ajaxInsertNodesPaths', 'FloorplanController@ajaxInsertNodesPaths');
    Route::post('/floorplan/ajaxAddBeacon', 'FloorplanController@ajaxAddBeacon');

    Route::post('/floorplan/ajaxInsertRelation', 'FloorplanController@ajaxInsertRelation');
    Route::post('/floorplan/ajaxUpdateCordinates', 'FloorplanController@ajaxUpdateCordinates');
    Route::post('/floorplan/RemoveNodeFromFloorplan', 'FloorplanController@RemoveNodeFromFloorplan');
    Route::post('/floorplan/RemoveBeaconFromFloorplan', 'FloorplanController@RemoveBeaconFromFloorplan');
    Route::post('/floorplan/SaveNodeEdits', 'FloorplanController@SaveNodeEdits');
    Route::post('/floorplan/SaveBeaconEdits', 'FloorplanController@SaveBeaconEdits');

    Route::post('/floorplan/ajaxGetAvailableGateWay', 'FloorplanController@ajaxGetAvailableGateWay');

    Route::get('data-table-floorplan', 'FloorplanController@FloorplanData');
    /*Route::get('/', function () {
        dd('This is the IPS module index page. Build something great!');
    });*/
});
