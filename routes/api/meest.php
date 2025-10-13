<?php

use Illuminate\Support\Facades\Route;
Route::group(['prefix' => "", 'middleware' => ['authorize_meest'], 'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME')], function () {
    Route::group(['prefix' => 'meest'], function () {
        Route::group(['prefix' => 'packages'], function () {
            Route::post('/', 'Package\PackageController@store');
            Route::get('/{parcel_id}', 'Package\PackageController@show');
            Route::put('/', 'Package\PackageController@update');
            Route::delete('/{parcel_id}', 'Package\PackageController@delete');

            Route::get('/{parcel_id}/states', 'Package\PackageStateController@show');
            Route::put('/{parcel_id}/states', 'Package\PackageStateController@update');
            Route::get('/{parcel_id}/etgb', 'Package\PackageController@etgb');
        });

        Route::get('status', 'Package\TrackingController@status');

        Route::group(['prefix' => 'warehouses', 'as' => 'warehouse.'], function () {
            Route::get('/', 'Warehouse\WarehouseController@index');
            Route::get('/{id}', 'Warehouse\WarehouseController@show');
        });

        Route::group(['prefix' => 'pallets', 'as' => 'pallet.'], function () {
            Route::post('/', 'Pallet\PalletController@store');
            Route::put('/', 'Pallet\PalletController@update');
        });

        Route::post('/shipments', 'Pallet\ShipmentController@finish');
    });
});
