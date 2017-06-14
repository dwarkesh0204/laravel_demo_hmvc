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

Route::group(['prefix' => 'proximity','middleware' => 'other_db_connection'], function () {

    // campaignManager routes start
    Route::resource("campaignManager","CampaignManagerController");
    Route::post('campaignManager/{id}',['as'=>'campaignManager.changeStatus','uses'=>'CampaignManagerController@changeStatus']);

    Route::get('data-table-adsManager', 'AdsManagerController@AdsManagerData');
    Route::get('data-table-campaignManager', 'CampaignManagerController@CampaignManagerData');
    Route::get('data-table-scheduleManager', 'ScheduleManagerController@ScheduleManagerData');

    Route::resource("adsManager","AdsManagerController");
    Route::post('adsManager/{id}',['as'=>'adsManager.changeStatus','uses'=>'AdsManagerController@changeStatus']);
    Route::post('adsManager/deleteimage',['as'=>'adsManager.deleteImage','uses'=>'AdsManagerController@deleteImage']);

    Route::resource("scheduleManager","ScheduleManagerController");
    Route::post('scheduleManager/{id}',['as'=>'scheduleManager.changeStatus','uses'=>'ScheduleManagerController@changeStatus']);

    //Route::get('data-table-subject', 'SubjectController@SubjectData');
    //subject routes ends

});
