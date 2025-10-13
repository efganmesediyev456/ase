<?php
Route::group([
    'domain' => env('PARTNER_SUB') . '.' . env('DOMAIN_NAME'),
    'namespace' => 'Warehouse',
], function () {

    App::setLocale('en');

    Route::group(['middleware' => ['auth:worker', 'panel']], function () {

        Route::get('/', [
            'as' => 'my.dashboard',
            'uses' => 'MainController@check',
        ]);

        Route::get('/users', [
            'as' => 'my.users',
            'uses' => 'MainController@users',
        ]);

        Route::get('/dashboard', [
            'as' => 'my.index',
            'uses' => 'MainController@check',
        ]);

        Route::get('edit/{id?}', [
            'as' => 'my.edit',
            'uses' => 'UserController@edit',
        ]);

        Route::put('edit/{id?}', [
            'as' => 'my.update',
            'uses' => 'UserController@update',
        ]);

        Route::resource('w-packages', 'MainController', [
            'parameters' => [
                'w-packages' => 'id',
            ],
        ]);

        Route::resource('w-newtypes', 'NewtypeController', [
            'parameters' => [
                'w-newtypes' => 'id',
            ],
        ]);

        Route::get('w-process', [
            'as' => 'w-process',
            'uses' => 'ParcellingController@package_process',
        ]);

        Route::get('w-packages/utexport/{id}', [
            'as' => 'w-packages.utexport',
            'uses' => 'MainController@utExport'
        ]);

        Route::get('w-packages/export/{items?}', [
            'as' => 'w-packages.export',
            'uses' => 'MainController@export'
        ]);

        Route::get('w-packages/manifest/{items?}', [
            'as' => 'w-packages.manifest',
            'uses' => 'MainController@manifest'
        ]);

        Route::get('w-processed/{status?}', [
            'as' => 'w-processed',
            'uses' => 'ProcessedController@index',
        ]);

        Route::post('w-processed/detach/{id?}/{package_id?}', [
            'as' => 'w-parceling.detach',
            'uses' => 'ParcellingController@deletePackage',
        ]);

        Route::get('my-package/{id}/label', [
            'as' => 'w-packages.label',
            'uses' => 'MainController@label'
        ]);

        Route::get('my-package/{id}/modal', [
            'as' => 'w-packages.modal',
            'uses' => 'MainController@modal'
        ]);

	Route::post('/w-packages/typenames/', [
	    'as' => 'w-packages.typenames',
	    'uses' => 'MainController@getRuTypeNameAutocomplete'
        ]);


        Route::post('my-package/{id}/ajax', [
            'as' => 'w-packages.ajax',
            'uses' => 'MainController@ajax'
        ]);
        Route::get('barcode/{code?}', [
            'as' => 'warehouse.barcode.scan',
            'uses' => 'MainController@barcodeScan'
        ]);

        Route::post('my-package/multiple', [
            'as' => 'w-packages.multiple',
            'uses' => 'MainController@multiUpdate'
        ]);

        Route::post('my-parcel/{id}/add-package', [
            'as' => 'w-parcel.add_package',
            'uses' => 'MainController@addPackage'
        ]);

        Route::resource('w-parcels', 'ParcellingController', [
            'parameters' => [
                'w-parcels' => 'id',
            ],
        ]);

       /* Route::match(['get', 'post'],'parcels/create', [
            'as' => 'w-parcels.create',
            'uses' => 'ParcellingController@parcelCreate'
        ]);*/

        Route::match(['get'],'parcels', [
            'as' => 'w-parcels.index',
            'uses' => 'ParcellingController@index'
        ]);

        Route::post('parcels/departed/{id}', [
            'as' => 'w-parcels.departed',
            'uses' => 'ParcellingController@departed'
        ]);

        Route::post('parcels/sent/{id}', [
            'as' => 'w-parcels.sent',
            'uses' => 'ParcellingController@sent'
        ]);

        Route::post('parcels/insert/{id}', [
            'as' => 'w-parcels.insert',
            'uses' => 'ParcellingController@insert'
        ]);
    });

    require 'auth.php';
    Route::get('my-package/{id}.pdf', [
        'as' => 'w-packages.pdf_label',
        'uses' => 'MainController@PDFLabel'
    ]);

    Route::get('invoice/{id}.pdf', [
        'as' => 'custom_invoice',
        'uses' => 'MainController@PDFInvoice'
    ]);
    Route::get('label/{id}.pdf', [
        'as' => 'label',
        'uses' => 'MainController@PDFLabel'
    ]);
});
