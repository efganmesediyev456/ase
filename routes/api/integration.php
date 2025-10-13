<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => "integration/", 'middleware' => ['authorize_integration'], 'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME')], function () {

    Route::group(['prefix' => '{company}'], function () {

        Route::group(['prefix' => 'packages'], function () {
            Route::post('/', 'Temu\Package\PackageController@store');
            Route::get('/{parcel_id}', 'Temu\Package\PackageController@show');
            Route::put('/', 'Temu\Package\PackageController@update');
            Route::delete('/{parcel_id}', 'Temu\Package\PackageController@delete');

            Route::get('/{parcel_id}/states', 'Temu\Package\PackageStateController@show');
            Route::put('/{parcel_id}/states', 'Temu\Package\PackageStateController@update');
            Route::get('/{parcel_id}/etgb', 'Temu\Package\PackageController@etgb');
        });

        Route::get('status', 'Temu\Package\TrackingController@status');

        Route::group(['prefix' => 'warehouses', 'as' => 'warehouse.'], function () {
            Route::get('/', 'Temu\Warehouse\WarehouseController@index');
            Route::get('/{id}', 'Temu\Warehouse\WarehouseController@show');
        });

        Route::group(['prefix' => 'pallets', 'as' => 'pallet.'], function () {
            Route::post('/', 'Temu\Pallet\PalletController@store');
            Route::put('/', 'Temu\Pallet\PalletController@update');
        });

        Route::post('/shipments', 'Temu\Pallet\ShipmentController@finish');
    });
});

Route::group(['prefix' => "gli/", 'middleware' => ['authorize_integration', 'logger'], 'domain' => env('API_SUB') . '.' . env('DOMAIN_NAME')], function () {
    Route::post('dwp.serpens.order_apply/{id}', 'Gfs\Package\PackageController@store');
    Route::post('dwp.serpens.track_push/{id}', 'Gfs\Package\PackageController@store');
    Route::post('dwp.serpens.mawb_info_push/{id}', 'Gfs\Pallet\ShipmentController@store');
    Route::post('dwp.serpens.waybill_instruction/{id}', 'Gfs\Pallet\ShipmentController@store');
});
