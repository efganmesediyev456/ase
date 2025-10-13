<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => "unitrade/", 'middleware' => ['authorize_unitrade'], 'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME')], function () {

    Route::group(['prefix' => '{company}'], function () {

        Route::group(['prefix' => 'packages'], function () {
            Route::post('/', 'Ozon\Package\PackageController@store');
            Route::get('/{parcel_id}', 'Ozon\Package\PackageController@show');
            Route::put('/', 'Ozon\Package\PackageController@update');
            Route::put('/test', 'Ozon\Package\PackageController@updatetest');
            Route::delete('/{parcel_id}', 'Ozon\Package\PackageController@delete');

            Route::get('/{parcel_id}/states', 'Ozon\Package\PackageStateController@show');
            Route::put('/{parcel_id}/states', 'Ozon\Package\PackageStateController@update');
            Route::get('/{parcel_id}/etgb', 'Ozon\Package\PackageController@etgb');
        });

        Route::get('status', 'Ozon\Package\TrackingController@status');

        Route::group(['prefix' => 'warehouses', 'as' => 'warehouse.'], function () {
            Route::get('/', 'Ozon\Warehouse\WarehouseController@index');
            Route::get('/{id}', 'Ozon\Warehouse\WarehouseController@show');
        });

        Route::group(['prefix' => 'pallets', 'as' => 'pallet.'], function () {
            Route::post('/', 'Ozon\Pallet\PalletController@store');
            Route::put('/', 'Ozon\Pallet\PalletController@update');
        });

        Route::post('/shipments', 'Ozon\Pallet\ShipmentController@finish');
    });
});
