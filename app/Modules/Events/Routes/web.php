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

Route::group(['prefix' => 'events', 'middleware' => 'other_db_connection'], function () {

	//Menu routes start
    Route::resource("menu","MenuController");
    Route::get('data-table-menu', 'MenuController@MenuData');
    Route::post('menu/{id}/changeStatus',['as'=>'menu.changeStatus','uses'=>'MenuController@changeStatus']);
    Route::delete('menu',['as'=>'menu.destroy','uses'=>'MenuController@destroy','middleware' => ['permission:item-delete']]);
    Route::post('menu/saveOrder',['as'=>'menu.saveOrder','uses'=>'MenuController@saveOrder']);
    //Menu routes ends

    //User routes start
    Route::resource("users","UserController");
    Route::get('data-table-users', 'UserController@UserData');
    Route::get('import',['as'=>'users.import','uses'=>'UserController@importCreate']);
    Route::post('uplodCSV',['as'=>'users.uplodCSV','uses'=>'UserController@uplodCSV']);
    Route::post('mapUserStore',['as'=>'users.mapUserStore','uses'=>'UserController@mapUserStore']);
    //User routes ends

    //Category routes start
    Route::resource("category","CategoriesController");
    Route::post('category/{id}/changeStatus',['as'=>'category.changeStatus','uses'=>'CategoriesController@changeStatus']);
    Route::get('data-table-Categories', 'CategoriesController@CategoriesData');
    //Category routes ends

    //CMS PAGES routes start
    Route::resource("cmspages","CmspageController");
    Route::post('cmspages/{id}/changeStatus',['as'=>'cmspages.changeStatus','uses'=>'CmspageController@changeStatus']);
    Route::get('data-table-cmspages', 'CmspageController@CmspagesData');

    //Email Template routes start
    Route::resource("email_template","EmailTemplateController");
    Route::post('email_template/{id}/changeStatus',['as'=>'email_template.changeStatus','uses'=>'EmailTemplateController@changeStatus']);
    Route::get('data-table-email_template', 'EmailTemplateController@EmailTemplateData');
    //Email Template routes ends

    //Roles routes start
    Route::resource("roles","RoleController");
    Route::post('roles/{id}/changeStatus',['as'=>'roles.changeStatus','uses'=>'RoleController@changeStatus']);
    Route::get('data-table-role', 'RoleController@RolesData');
    //Roles routes ends

    //Questions routes start
    Route::resource("question","QuestionController");
    Route::post('question/{id}/changeStatus',['as'=>'question.changeStatus','uses'=>'QuestionController@changeStatus']);
    Route::get('data-table-question', 'QuestionController@QuestionData');
    //Questions routes ends

    //fields routes start
    Route::resource("fields","FieldsController");
    Route::post('fields/{id}/changeStatus',['as'=>'fields.changeStatus','uses'=>'FieldsController@changeStatus']);
    Route::post('fields/storeGroup',['as'=>'fields.storeGroup','uses'=>'FieldsController@storeGroup']);
    Route::post('fields/getGroupDetail',['as'=>'events.getGroupDetail','uses'=>'FieldsController@getGroupDetail']);
    //Route::get('data-table-floorplan', 'FloorplanController@QuestionData');
    //fields routes ends

    //Events routes start
    Route::resource("events","EventsController");
    Route::get('data-table-events', 'EventsController@EventsData');
    Route::post('events/{id}/changeStatus',['as'=>'events.changeStatus','uses'=>'EventsController@changeStatus']);
    Route::post('events/storeGroup',['as'=>'events.storeGroup','uses'=>'EventsController@storeGroup']);
    Route::post('events/deleteCover',['as'=>'events.deleteCover','uses'=>'EventsController@deleteCover']);
    Route::post('events/uploaddocumentviaurl',['as'=>'events.uploaddocumentviaurl','uses'=>'EventsController@DocumentUrlAction']);
    Route::post('{id}/image-operation',['as'=>'events.ImageAction','uses'=>'EventsController@ImageAction']);
    Route::post('{id}/image-url-operation',['as'=>'events.ImageUrlAction','uses'=>'EventsController@ImageUrlAction']);
    Route::post('getuploadfilelist',['as'=>'events.getuploadfilelist','uses'=>'EventsController@GetUploadFileList']);

    Route::post('events/uploadfiletoinvoice',['as'=>'events.uploadfiletoinvoice','uses'=>'EventsController@uploadfiletoinvoice']);
    Route::post('events/document-delete',['as'=>'events.document-delete','uses'=>'EventsController@DocumentDeleteAction']);

    Route::post('{id}/image-remove',['as'=>'events.removeImage','uses'=>'EventsController@removeImage']);
    Route::post('{id}/video-remove',['as'=>'events.removeVideo','uses'=>'EventsController@removeVideo']);
    Route::post('{id}/doc-remove',['as'=>'events.removeDocument','uses'=>'EventsController@removeDocument']);

    Route::post('events/ticketchangeStatus/{id}',['as'=>'events.ticketchangeStatus','uses'=>'EventsController@ticketchangeStatus']);
    Route::post('getTicketRecords',['as'=>'events.getTicketRecords','uses'=>'EventsController@getTicketRecords']);
    Route::delete('events/removeTicket/',['as'=>'eventTicket.ticketDestroy','uses'=>'EventsController@ticketDestroy']);
    Route::post('events/saveTicket/',['as'=>'events.storeTicket','uses'=>'EventsController@storeTicket']);
    //Route::get('data-table-floorplan', 'FloorplanController@QuestionData');
    //fields routes ends

    //User Event routes start
    Route::resource("userRequestForEvent","UserRequestForEventController");
    Route::delete('user-event-request',['as'=>'UserRequestForEvent.destroy','uses'=>'UserRequestForEventController@destroy']);
    //User Event

    Route::resource("userRequestForSession","UserRequestForSessionController");
    Route::post('sessions/change-status/{id}',['as'=>'UserRequestForSession.changeStatus','uses'=>'UserRequestForSessionController@changeStatus']);
    Route::delete('user-session-request',['as'=>'UserRequestForSession.destroy','uses'=>'UserRequestForSessionController@destroy']);

    //Session routes start
    Route::resource("sessions", "SessionsController");
    Route::post('sessions/change-status/{id}',['as'=>'sessions.changeStatus','uses'=>'SessionsController@changeStatus']);
    //Session routes end

    //Poll Question routes start
    Route::resource("pollquestion", "PollingController");
    Route::post('pollquestion/change-status/{id}',['as'=>'pollquestion.changeStatus','uses'=>'PollingController@changeStatus']);
    //Poll Question routes end

    //Feedback routes//
    Route::resource("feedback", "FeedbackController");
    Route::delete('feedback',['as'=>'feedback.destroy','uses'=>'FeedbackController@destroy']);
    Route::post('feedback/{id}',['as'=>'feedback.changeStatus','uses'=>'FeedbackController@changeStatus']);
    // End //

    Route::resource("floorplans","FloorplanController");
    Route::delete('floorplans',['as'=>'floorplans.destroy','uses'=>'FloorplanController@destroy']);

    // Floorplan AJAX Routes //
    Route::post('/floorplans/ajaxExport', 'FloorplanController@ajaxExport');
    Route::post('/floorplans/ajaxInsertNodesPaths', 'FloorplanController@ajaxInsertNodesPaths');
    Route::post('/floorplans/ajaxAddBeacon', 'FloorplanController@ajaxAddBeacon');

    Route::post('/floorplans/ajaxInsertRelation', 'FloorplanController@ajaxInsertRelation');
    Route::post('/floorplans/ajaxUpdateCordinates', 'FloorplanController@ajaxUpdateCordinates');
    Route::post('/floorplans/RemoveNodeFromFloorplan', 'FloorplanController@RemoveNodeFromFloorplan');
    Route::post('/floorplans/RemoveBeaconFromFloorplan', 'FloorplanController@RemoveBeaconFromFloorplan');
    Route::post('/floorplans/SaveNodeEdits', 'FloorplanController@SaveNodeEdits');
    Route::post('/floorplans/SaveBeaconEdits', 'FloorplanController@SaveBeaconEdits');

    Route::post('/floorplans/ajaxGetAvailableGateWay', 'FloorplanController@ajaxGetAvailableGateWay');

    Route::get('data-table-floorplan', 'FloorplanController@FloorplanData');

});

Route::group(['prefix' => 'events'], function ()  {
    Route::post('webservice', 'WebserviceController@index');
});
