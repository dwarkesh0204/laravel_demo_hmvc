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

Route::group(['prefix' => 'storesolution','middleware' => 'other_db_connection'], function () {

    //stores routes start
    Route::resource("stores","StoreController");
    Route::post('stores/{id}/changeStatus',['as'=>'stores.changeStatus','uses'=>'StoreController@changeStatus']);
    Route::get('data-table-store', 'StoreController@StoreData');
    Route::post('stores/deleteimage',['as'=>'stores.deleteImage','uses'=>'StoreController@deleteImage']);
    //stores routes ends
});
