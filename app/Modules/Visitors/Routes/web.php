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
Route::group(['prefix' => 'visitors','middleware' => 'other_db_connection'], function () {
	//visitors routes start
    Route::resource("visitors","VisitorController");
    Route::post('visitors/{id}/changeStatus',['as'=>'visitors.changeStatus','uses'=>'VisitorController@changeStatus']);
    Route::get('data-table-visitor', 'VisitorController@VisitorData');
    //visitors routes ends
});
Route::group(['prefix' => 'visitors'], function ()  {
    //visitors routes start
    /*Route::post('webservice', 'WebserviceController@index');*/
    Route::post('visitor_log', 'VisitorHadoopController@visitor_log');
    //visitors routes ends
});
