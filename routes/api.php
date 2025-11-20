<?php

use App\Http\Controllers\Api\AzeriExpressController;
use App\Http\Controllers\Api\AzerpostController;
use App\Http\Controllers\Api\VerificationCodesController;
use Illuminate\Support\Facades\Route;

Route::group([
    'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME'),
    'namespace' => 'Api',
    'prefix' => 'v1',
    'middleware' => ['throttle:20000'],
], function () {
    Route::match(['post', 'get'], '/azeriexpress/update-package-status', [
        'uses' => 'AzeriExpressController@updateStatus',
        'as' => 'azeriexpress.update-package-status',
    ]);

    Route::match(['post', 'get'], '/azeriexpress/update-courier-status', [
        'uses' => 'AzeriExpressController@updateCourierStatus',
        'as' => 'azeriexpress.update-courier-status',
    ]);

    Route::match(['post', 'get'], '/yenipoct/update-package-status', [
        'uses' => 'YenipoctController@updateStatus',
        'as' => 'yenipoct.update-package-status',
    ]);

    Route::match(['post', 'get'], '/azerpost/update-package-status', [
        'uses' => 'AzerpostController@updateStatus',
        'as' => 'azerpost.update-package-status',
    ]);

    Route::match(['post', 'get'], '/surat/update-package-status', [
        'uses' => 'SuratController@updateStatus',
        'as' => 'surat.update-package-status',
    ]);


    Route::match(['post', 'get'], '/surat/update-package-status2', [
        'uses' => 'SuratController@updateStatus2',
        'as' => 'surat.update-package-status2',
    ]);


    Route::group([
        'prefix' => '/kuryera',
    ], function(){
        Route::get('/parcels/{tracking}', [
            'uses' => 'KuryeraController@getParcel',
            'as' => 'kuryera.get-parcel',
        ]);
        Route::match(['post', 'get'], '/update-courier-status', [
            'uses' => 'KuryeraController@updateCourierStatus',
            'as' => 'kuryera.update-courier-status',
        ]);
    });
});

//IniDesk
Route::group([
    'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME'),
    'namespace' => 'Api',
    'prefix' => 'inidesk',
    'middleware' => ['throttle:20000'],
], function () {
    Route::get('get-data', [
        'uses' => 'InideskController@getDatas',
    ]);
});

Route::group([
    'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME'),
    'namespace' => 'Api',
    'prefix' => 'getLocation',
    'middleware' => ['throttle:20000'],
], function () {
    Route::post('', [
        'uses' => 'CourierLocationController@getLocation',
    ]);
});


Route::group([
    'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME'),
    'namespace' => 'Api',
    'middleware' => ['throttle:200'],
    'prefix' => 'v1',
], function () {

    Route::get('/', function () {
        return "It works";
    });

    Route::post('/verification-codes', 'VerificationCodesController@store');

    Route::get('track/{code}', [
        'uses' => 'ExtraController@track',
    ]);

    Route::group([
        'middleware' => ['w_api'],
    ], function () {
        Route::get('user', [
            'uses' => 'ExtraController@user',
        ]);

        Route::post('package/add', [
            'uses' => 'ExtraController@add',
        ]);

        Route::post('package/send', [
            'uses' => 'ExtraController@send',
        ]);
    });
});
