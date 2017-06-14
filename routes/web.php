<?php

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


Route::get('/', 'HomeController@index');


Auth::routes();

Route::get('/home', 'HomeController@index');
Route::resource("projects","ProjectsController");
Route::delete('projects/destory',['as'=>'projects.destroy','uses'=>'ProjectsController@destroy','middleware' => ['auth']]);
Route::post('projects/{id}/changeStatus',['as'=>'projects.changeStatus','uses'=>'ProjectsController@changeStatus','middleware' => ['auth']]);
Route::post('projects/{id}/access',['as'=>'projects.access','uses'=>'ProjectsController@access','middleware' => ['auth']]);
Route::get('session_error',['as'=>'projects.session_error','uses'=>'ProjectsController@sessionError']);
Route::post('updateWidgetOrdering','HomeController@ajaxUpdateWidgetOrdering');
Route::get('getTestLog','HomeController@getTestLog');
