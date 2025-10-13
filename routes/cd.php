<?php
Route::group([
    'domain' => env('CD_SUB') . '.' . env('DOMAIN_NAME'),
    'namespace' => 'Cd',
], function () {

    App::setLocale('en');

    Route::post('api/login', 'ApiController@login');
    Route::middleware('courierauth')->post('api/cd_list', 'ApiController@cd_list');
    Route::middleware('courierauth')->put('api/cd_courier_comment', 'ApiController@cd_set_courier_comment');
    Route::middleware('courierauth')->put('api/cd_status', 'ApiController@cd_set_status');
    Route::middleware('courierauth')->put('api/cd_statuses', 'ApiController@cd_set_statuses');
    Route::middleware('courierauth')->put('api/cd_nd_status', 'ApiController@cd_set_nd_status');
    Route::middleware('courierauth')->put('api/cd_nd_statuses', 'ApiController@cd_set_nd_statuses');
    Route::middleware('courierauth')->post('api/cd_photo', 'ApiController@cd_photo');
    Route::middleware('courierauth')->post('api/cd_photos', 'ApiController@cd_photos');
    Route::middleware('courierauth')->post('api/ping', 'ApiController@ping');
    Route::middleware('courierauth')->put('api/cd_location', 'ApiController@cd_set_location');
    Route::middleware('courierauth')->put('api/cd_locations', 'ApiController@cd_set_locations');
    Route::middleware('courierauth')->put('api/cd_new_vfs', 'ApiController@cd_new_vfs');
    Route::middleware('courierauth')->put('api/new_location', 'ApiController@new_location');
    Route::middleware('courierauth')->put('api/update_location', 'ApiController@update_location');

    Route::group(['middleware' => ['auth:courier', 'panel']], function () {

        Route::get('/', [
            'as' => 'cd.dashboard',
            'uses' => 'MainController@check',
        ]);

        Route::get('/dashboard', [
            'as' => 'cd.index',
            'uses' => 'MainController@check',
        ]);

        Route::get('/cd', [
            'as' => 'cd',
            'uses' =>  'CourierDeliveryController@index',
        ]);

        Route::post('/cd/ajax/{id?}', [
            'as' => 'cd.ajax',
            'uses' =>  'CourierDeliveryController@ajax',
        ]);

        Route::get('/cd/info/{id?}', [
            'as' => 'cd.info',
            'uses' =>  'CourierDeliveryController@info',
        ]);

	$resources = [
 	    'courier_delivery' => [
	        ['name' => 'ajax', 'method' => 'post'],
	    ],	
        ];  
    });

    require 'auth.php';
});
