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

Route::group(['prefix' => 'attendance','middleware' => 'other_db_connection'], function () {
    //subject routes start
    Route::resource("subject","SubjectController");
    Route::get('data-table-subject', 'SubjectController@SubjectData');
    Route::post('get-semesters', 'SubjectController@getSemesterData');
    Route::post('get-subjects', 'SubjectController@getSubjectsData');
    //subject routes ends

    //Notification routes start
    Route::resource("notifications","NotificationsController");
    Route::get('data-table-notifications', 'NotificationsController@NotificationsData');
    //Notification routes ends

    //Lectures routes start
    Route::resource("lectures","LecturesController");
    Route::get('data-table-lectures', 'LecturesController@LecturesData');
    //Lectures routes ends

    //Class routes start
    Route::resource('class','ClassController');
	Route::get('data-table-class', 'ClassController@ClassData');
    //Class routes ends

	//Course routes start
    Route::resource('course','CourseController');
	Route::get('data-table-course', 'CourseController@CourseData');
	//Course routes ends

    //Semester routes start
    Route::resource('semester','SemesterController');
    Route::get('data-table-semester', 'SemesterController@SemesterData');
    //Semester routes ends

    //Students routes start
    Route::resource('students','StudentsController');
    Route::get('data-table-students', 'StudentsController@StudentsData');
    //Semester routes ends

    //teachers routes start
    Route::resource('teachers','TeachersController');
    Route::get('data-table-teachers', 'TeachersController@TeachersData');
    Route::post('teachers/deleteimage',['as'=>'teachers.deleteImage','uses'=>'TeachersController@deleteImage']);
    //teachers routes ends

});
Route::group(['prefix' => 'attendance'], function ()  {
    //subject routes start
    Route::post('webservice', 'WebserviceController@index');
    Route::post('attendance_log', 'AttendanceHadoopController@attendance_log');
    //subject routes ends
});
