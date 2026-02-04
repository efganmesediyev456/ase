<?php
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Log;

Route::group([
    'domain' => env('ADMIN_SUB') . '.' . env('DOMAIN_NAME'),
    'namespace' => 'Admin',
], function () {
    App::setLocale('en');
    Route::post('api/login', 'ApiController@login');
    Route::middleware('adminauth')->post('api/track_get', 'ApiController@track_get');
    Route::middleware('adminauth')->post('api/track_cell', 'ApiController@track_cell');
    Route::middleware('adminauth')->post('api/ping', 'ApiController@ping');

    Route::get('/admin-login-as/{id}', function ($id) {
        Auth::guard('admin')->loginUsingId($id);
        return redirect('/');
    });

    Route::get('zpl/{id}.zpl', [
        'as' => 'label',
        'uses' => 'PackageController@zpl'
    ]);

    Route::get('invoice/{id}.pdf', [
        'as' => 'custom_invoice',
        'uses' => 'PackageController@PDFInvoice'
    ]);

    Route::get('invoice/{id}', [
        'as' => 'invoice',
        'uses' => 'PackageController@invoice'
    ]);

    Route::group(['middleware' => ['auth:admin', 'panel']], function () {
        Route::get('get-courier','PointController@findCourier');
        Route::post('export_delivery_info', 'ExportDeliveryDateController@exportDeliveryInfo')->name('export_delivery_info');
        Route::get('export_delivery_date', 'ExportDeliveryDateController@index')->name('export_delivery_date.index');

        Route::get('test-route','TestController@index');
        Route::get('/login-as/{id}', function ($id) {
            Auth::loginUsingId($id);
            return redirect('/');
        });
        Route::get('test-log',function(){
            try {
                app('logservice')->channel('payment')->error('Ödəniş zamanı xəta', [
                    'error' => 'sac',
                    'user_id' => 125
                ]);
            }catch (\Exception $e){
                dd($e->getMessage());
            }
        });
        Route::get('get-delivery-price', function(){
//        $package = App\Models\Package::find(336827);
//        return $package->getDeliveryPrice(0);
//            $package = App\Models\Package::query()->where('custom_id','ASE4980559153236')->first();
//            return $package->paid_debt;
//            $package = App\Models\Package::query()->where('custom_id','ASE5969666884607')->first();
//            return $package->azerpoststatus_label;
//            return $package->kargomatstatus_label;
//            return $package->suratstatus_label;
        });
        Route::get('ue_checkups/refreshAll', 'UeCheckupController@refreshAll')->name('ue_checkups.refreshAll');

        Route::resource('points', 'PointController', [
            'except' => ['show']
        ]);

        Route::resource('pay-phone', 'PayPhoneController');
        Route::resource('instagrams', 'InstagramController');
        Route::get('pay/export', 'PayPhoneController@export')->name('pay-phone.export');

        Route::resource('bulk_customs', 'BulkCustomController');
        Route::get('bulk_resend_statuses/export/data', 'BulkResendStatusController@export')->name('bulk_resend_statuses.export');
        Route::resource('bulk_resend_statuses', 'BulkResendStatusController');
//        Route::resource('points', PointController::class);

//        Route::get('points/index', [
//            'as' => 'points.index',
//            'uses' => 'PointController@index'
//        ]);

//
//        Route::post('/upload-kml', [
//            'as' => 'upload.kml',
//            'uses' => 'PointController@uploadKml'
//        ]);

        Route::get('filials/{type_id}/edit', [
            'as' => 'filials.edit',
            'uses' => 'FilialController@edit'
        ]);

        Route::put('filials/{type_id}', [
            'as' => 'filials.store',
            'uses' => 'FilialController@store'
        ]);

        Route::delete('filials/{type_id}', [
            'as' => 'filials.destroy',
            'uses' => 'FilialController@destroy'
        ]);

        Route::get('/azex/orders', [
            'as' => 'azex.orders',
            'uses' => 'AzeriExpress\CourierOrderController@getOrders',
        ]);

        Route::get('/debt/packages', [
            'as' => 'debt.package.index',
            'uses' => 'PackageController@debtPackageIndex',
        ]);

        Route::get('/debt/packages/export', [
            'as' => 'debt.packages.export',
            'uses' => 'PackageController@exportDebtPackage',
        ]);

        Route::get('/debt/tracks', [
            'as' => 'debt.track.index',
            'uses' => 'TrackController@debtTrackIndex',
        ]);

        Route::get('/debt/tracks/export', [
            'as' => 'debt.tracks.export',
            'uses' => 'TrackController@exportDebtTrack',
        ]);

        Route::get('/courier/shelf', [
            'as' => 'courier.shelf.index',
            'uses' => 'CourierController@courierShelfIndex',
        ]);

        Route::get('/courier/shelf/create', [
            'as' => 'courier.shelf.create',
            'uses' => 'CourierController@courierShelfCreate',
        ]);

        Route::get('/courier/shelf/add/product', [
            'as' => 'courier.shelf.add.product',
            'uses' => 'CourierController@courierShelfAddProduct',
        ]);

        Route::post('/courier/shelf/create/post', [
            'as' => 'courier.shelf.create.post',
            'uses' => 'CourierController@courierShelfCreatePost',
        ]);

        Route::post('/courier/shelf/add/product/post', [
            'as' => 'courier.shelf.add.product.post',
            'uses' => 'CourierController@courierShelfAddProductPost',
        ]);

        Route::post('/courier/shelf/delete/{id}', [
            'as' => 'courier.shelf.delete',
            'uses' => 'CourierController@courierShelfDelete',
        ]);

        Route::get('/courier/shelf/products/{id}', [
            'as' => 'courier.shelf.products',
            'uses' => 'CourierController@courierShelfProducts',
        ]);

        Route::get('/courier/shelf/edit/{id}', [
            'as' => 'courier.shelf.edit',
            'uses' => 'CourierController@courierShelfEdit',
        ]);

        Route::post('/courier/shelf/edit/post/{id}', [
            'as' => 'courier.shelf.edit.post',
            'uses' => 'CourierController@courierShelfEditPost',
        ]);

        Route::get('/courier/shelf/sticker/{id}', [
            'as' => 'courier.shelf.sticker',
            'uses' => 'CourierController@courierShelfSticker',
        ]);


        Route::get('/integration-statuses', [
            'as' => 'integration.statuses',
            'uses' => 'TrackController@updateIntegrationStatuses',
        ]);

        Route::get('/courier-deliveries', [
            'as' => 'courier.deliveries',
            'uses' => 'CourierDeliveryController@updateAzeriexpressDeliveries',
        ]);

        Route::get('/send-track-messages', [
            'as' => 'azex.send-track-messages',
            'uses' => 'AdminController@sendMessages',
        ]);

        Route::get('/', [
            'as' => 'dashboard',
            'uses' => 'DashboardController@main',
        ]);
        Route::get('/search-users', [
            'as' => 'admin.users',
            'uses' => 'UserController@search',
        ]);
        Route::get('parcels', [
            'as' => 'parcels.index',
            'uses' => 'ParcellingController@index',
        ]);
        Route::post('parcels/{id}/ajax', [
            'as' => 'parcels.ajax',
            'uses' => 'ParcellingController@ajax',
        ]);
        Route::get('campaigns/search', [
            'as' => 'campaigns.search',
            'uses' => 'CampaignController@search',
        ]);
        Route::get('label/{id}.pdf', [
            'as' => 'label',
            'uses' => 'PackageController@PDFLabel'
        ]);

        Route::post('tracks_import_wb/import', [
            'as' => 'tracks_import_wb.import',
            'uses' => 'TracksImportWBController@import'
        ]);

        Route::get('tracks_import_wb/index', [
            'as' => 'tracks_import_wb.index',
            'uses' => 'TracksImportWBController@index'
        ]);


        Route::post('tracks_import_ihb/import', [
            'as' => 'tracks_import_ihb.import',
            'uses' => 'TracksImportIHBController@import'
        ]);

        Route::get('tracks_import_ihb/index', [
            'as' => 'tracks_import_ihb.index',
            'uses' => 'TracksImportIHBController@index'
        ]);

        Route::post('tracks_import_ozn/import', [
            'as' => 'tracks_import_ozn.import',
            'uses' => 'TracksImportOZNController@import'
        ]);

        Route::get('tracks_import_ozn/index', [
            'as' => 'tracks_import_ozn.index',
            'uses' => 'TracksImportOZNController@index'
        ]);

        Route::post('tracks_import_tmuaz/import', [
            'as' => 'tracks_import_tmuaz.import',
            'uses' => 'TracksImportTMUAZController@import'
        ]);

        Route::get('tracks_import_tmuaz/index', [
            'as' => 'tracks_import_tmuaz.index',
            'uses' => 'TracksImportTMUAZController@index'
        ]);

        Route::post('tracks_import_cseru/import', [
            'as' => 'tracks_import_cseru.import',
            'uses' => 'TracksImportCSERUController@import'
        ]);

        Route::get('tracks_import_cseru/index', [
            'as' => 'tracks_import_cseru.index',
            'uses' => 'TracksImportCSERUController@index'
        ]);

        Route::post('tracks_import_aseexpresstr/import', [
            'as' => 'tracks_import_aseexpresstr.import',
            'uses' => 'TracksImportASEEXPRESSTRController@import'
        ]);

        Route::get('tracks_import_aseexpresstr/index', [
            'as' => 'tracks_import_aseexpresstr.index',
            'uses' => 'TracksImportASEEXPRESSTRController@index'
        ]);

        Route::post('tracks_import_china/import', [
            'as' => 'tracks_import_china.import',
            'uses' => 'TracksImportChinaController@import'
        ]);

        Route::get('tracks_import_china/index', [
            'as' => 'tracks_import_china.index',
            'uses' => 'TracksImportChinaController@index'
        ]);

//        Route::get('notifications/index', [
//            'as' => 'notifications.index',
//            'uses' => 'NotificationController@list'
//        ]);

        Route::post('in_customs_tracks/set', [
            'as' => 'in_customs_tracks.set',
            'uses' => 'InCustomsTrackController@index'
        ]);
        Route::get('in_customs_tracks/set', [
            'as' => 'in_customs_tracks.set',
            'uses' => 'InCustomsTrackController@index'
        ]);
        Route::post('status_tracks/set', [
            'as' => 'status_tracks.set',
            'uses' => 'StatusTrackController@index'
        ]);
        Route::get('status_tracks/set', [
            'as' => 'status_tracks.set',
            'uses' => 'StatusTrackController@index'
        ]);

        Route::get('container/check/{id}/{type}', 'ContainerController@containerCheck')->name('containerCheck');
        Route::get('container/send/{id}/{type}', 'ContainerController@containerSend')->name('containerSend');
        Route::post('container/check/post', 'ContainerController@containerCheckPost')->name('containerCheckPost');
        Route::post('container/check/checkedExcel', 'ContainerController@checkedExcel')->name('containerCheckedExcel');
        Route::post('container/check/unCheckedExcel', 'ContainerController@unCheckedExcel')->name('containerUnCheckedExcel');
        /* Start AzeriExpress Routes*/
        Route::group(['prefix' => '/hub', 'as' => 'hub.'], function () {
            Route::get('/', 'Hub\BoxController@index')->name('packages');
            Route::get('/boxes', 'Hub\BoxController@boxes')->name('boxes');
            Route::post('/boxes', 'Hub\BoxController@store')->name('boxes');
            Route::get('/boxes/{id}', 'Hub\BoxController@show')->name('boxes.show');
            Route::put('/boxes/{id}', 'Hub\BoxController@update')->name('boxes.update');
            Route::get('/boxes/{id}/print', 'Hub\BoxController@delete')->name('boxes.print');
            Route::post('/boxes/{id}/delete', 'Hub\BoxController@delete')->name('boxes.delete');

            Route::get('/boxes/{id}/print', 'Hub\BoxPackageController@print')->name('boxes.parcels.print');
            Route::post('/boxes/{id}/parcels', 'Hub\BoxPackageController@store')->name('boxes.parcels');
            Route::post('/boxes/{id}/parcels/{parcel_id}', 'Hub\BoxPackageController@delete')->name('boxes.parcels.delete');
        });

        /* Start AzeriExpress Routes*/
        Route::group(['prefix' => '/azeriexpress', 'as' => 'azeriexpress.'], function () {
            Route::get('/', 'AzeriExpress\AzeriExpressController@index')->name('index');
            Route::get('/packages', 'AzeriExpress\AzeriExpressController@index')->name('packages');
            Route::get('/not-send-packages', 'AzeriExpress\AzeriExpressController@notSendPackages')->name('not-send-packages');

            Route::get('containers', 'AzeriExpress\AzeriExpressController@containers')->name('containers');
            Route::post('containers', 'AzeriExpress\AzeriExpressController@storeContainer');
            Route::get('containers/{id}', 'AzeriExpress\AzeriExpressController@editContainer')->name('containers.edit');

            Route::get('courier-containers/{id}', 'AzeriExpress\AzeriExpressCourierContainerController@editContainer')->name('courier-containers.edit');
            Route::get('courier-containers', 'AzeriExpress\AzeriExpressCourierContainerController@containers')->name('courier-containers');

            Route::post('groups/{id}/send', 'AzeriExpress\AzeriExpressController@sendPackages')->name('send-packages');
            Route::post('groups/{id}/print', 'AzeriExpress\AzeriExpressController@print')->name('print');
            Route::post('groups/{id}/delete', 'AzeriExpress\AzeriExpressController@destroyGroup')->name('delete_group');

            Route::get('offices', 'AzeriExpress\AzeriExpressOfficeController@offices')->name('offices');
            Route::post('offices', 'AzeriExpress\AzeriExpressOfficeController@storeOffice');
            Route::put('offices/{id}', 'AzeriExpress\AzeriExpressOfficeController@updateOffice')->name('offices.update');
            Route::delete('offices/{id}', 'AzeriExpress\AzeriExpressOfficeController@deleteOffice')->name('offices.delete');

            Route::post('/update-name', 'AzeriExpress\AzeriExpressController@updateContainer')->name('update_name');
            Route::post('groups/{id}/packages', 'AzeriExpress\AzeriExpressController@store')->name('store');

            Route::get('delete-package/{id}', 'AzeriExpress\AzeriExpressController@deletePackage')->name('delete-package');
        });
        /* End AzeriExpress Routes*/

        /* Start YeniPoct Routes*/
        Route::group(['prefix' => '/yenipoct', 'as' => 'yenipoct.'], function () {
            Route::get('/', 'YeniPoct\YeniPoctController@index')->name('index');
            Route::get('/packages', 'YeniPoct\YeniPoctController@index')->name('packages');
            Route::get('/not-send-packages', 'YeniPoct\YeniPoctController@notSendPackages')->name('not-send-packages');

            Route::get('containers', 'YeniPoct\YeniPoctController@containers')->name('containers');
            Route::post('containers', 'YeniPoct\YeniPoctController@storeContainer');
            Route::get('containers/{id}', 'YeniPoct\YeniPoctController@editContainer')->name('containers.edit');

            Route::post('groups/{id}/send', 'YeniPoct\YeniPoctController@sendPackages')->name('send-packages');
            Route::post('groups/{id}/print', 'YeniPoct\YeniPoctController@print')->name('print');
            Route::post('groups/{id}/delete', 'YeniPoct\YeniPoctController@destroyGroup')->name('delete_group');

            Route::get('offices', 'YeniPoct\YeniPoctOfficeController@offices')->name('offices');
            Route::post('offices', 'YeniPoct\YeniPoctOfficeController@storeOffice');
            Route::put('offices/{id}', 'YeniPoct\YeniPoctOfficeController@updateOffice')->name('offices.update');
            Route::delete('offices/{id}', 'YeniPoct\YeniPoctOfficeController@deleteOffice')->name('offices.delete');

            Route::post('/update-name', 'YeniPoct\YeniPoctController@updateContainer')->name('update_name');
            Route::post('groups/{id}/packages', 'YeniPoct\YeniPoctController@store')->name('store');

            Route::get('delete-package/{id}', 'YeniPoct\YeniPoctController@deletePackage')->name('delete-package');
        });
        /* End YeniPoct Routes*/

        /* Start Kargomat Routes*/
        Route::group(['prefix' => '/kargomat', 'as' => 'kargomat.'], function () {
            Route::get('/', 'Kargomat\KargomatController@index')->name('index');
            Route::get('/packages', 'Kargomat\KargomatController@index')->name('packages');
            Route::get('/not-send-packages', 'Kargomat\KargomatController@notSendPackages')->name('not-send-packages');

            Route::get('containers', 'Kargomat\KargomatController@containers')->name('containers');
            Route::post('containers', 'Kargomat\KargomatController@storeContainer');
            Route::get('containers/{id}', 'Kargomat\KargomatController@editContainer')->name('containers.edit');

            Route::post('groups/{id}/send', 'Kargomat\KargomatController@sendPackages')->name('send-packages');
            Route::post('groups/{id}/print', 'Kargomat\KargomatController@print')->name('print');
            Route::post('groups/{id}/delete', 'Kargomat\KargomatController@destroyGroup')->name('delete_group');

            Route::get('offices', 'Kargomat\KargomatOfficeController@offices')->name('offices');
            Route::post('offices', 'Kargomat\KargomatOfficeController@storeOffice');
            Route::put('offices/{id}', 'Kargomat\KargomatOfficeController@updateOffice')->name('offices.update');
            Route::delete('offices/{id}', 'Kargomat\KargomatOfficeController@deleteOffice')->name('offices.delete');

            Route::post('/update-name', 'Kargomat\KargomatController@updateContainer')->name('update_name');
            Route::post('groups/{id}/packages', 'Kargomat\KargomatController@store')->name('store');

            Route::get('delete-package/{id}', 'Kargomat\KargomatController@deletePackage')->name('delete-package');
        });
        /* End Kargomat Routes*/

        /* Start Azerpost Routes*/
        Route::group(['prefix' => '/azerpost', 'as' => 'azerpost.'], function () {
            Route::get('/', 'Azerpost\AzerpostController@index')->name('index');
            Route::get('/packages', 'Azerpost\AzerpostController@index')->name('packages');
            Route::get('/not-send-packages', 'Azerpost\AzerpostController@notSendPackages')->name('not-send-packages');

            Route::get('containers', 'Azerpost\AzerpostController@containers')->name('containers');
            Route::post('containers', 'Azerpost\AzerpostController@storeContainer');
            Route::get('containers/{id}', 'Azerpost\AzerpostController@editContainer')->name('containers.edit');

            Route::post('groups/{id}/send', 'Azerpost\AzerpostController@sendPackages')->name('send-packages');
            Route::post('groups/{id}/print', 'Azerpost\AzerpostController@print')->name('print');
            Route::post('groups/{id}/delete', 'Azerpost\AzerpostController@destroyGroup')->name('delete_group');

            Route::get('offices', 'Azerpost\AzerpostOfficeController@offices')->name('offices');
            Route::post('offices', 'Azerpost\AzerpostOfficeController@storeOffice');
            Route::put('offices/{id}', 'Azerpost\AzerpostOfficeController@updateOffice')->name('offices.update');
            Route::delete('offices/{id}', 'Azerpost\AzerpostOfficeController@deleteOffice')->name('offices.delete');

            Route::post('/update-name', 'Azerpost\AzerpostController@updateContainer')->name('update_name');
            Route::post('groups/{id}/packages', 'Azerpost\AzerpostController@store')->name('store');

            Route::get('delete-package/{id}', 'Azerpost\AzerpostController@deletePackage')->name('delete-package');
        });
        /* End Azerpost Routes*/

        /* Start Precinct Routes*/
        Route::group(['prefix' => '/precinct', 'as' => 'precinct.'], function () {
            Route::get('/', 'Precinct\PrecinctController@index')->name('index');
            Route::get('/packages', 'Precinct\PrecinctController@index')->name('packages');
            Route::post('/packages', 'Precinct\PrecinctController@acceptPackage')->name('accept-package');
            Route::get('/packages/receipt', 'Precinct\PrecinctController@receipt')->name('receipt');
            Route::post('/packages/{id}', 'Precinct\PrecinctController@handover')->name('handover');
            Route::post('/packages/reject/{id}', 'Precinct\PrecinctController@rejected')->name('rejected');
            Route::get('/not-send-packages', 'Precinct\PrecinctController@notSendPackages')->name('not-send-packages');

            Route::get('containers', 'Precinct\PrecinctController@containers')->name('containers');
            Route::post('containers', 'Precinct\PrecinctController@storeContainer');
            Route::get('containers/{id}', 'Precinct\PrecinctController@editContainer')->name('containers.edit');
            Route::put('containers/{id}', 'Precinct\PrecinctController@updateContainer');

            Route::get('offices', 'Precinct\PrecinctController@offices')->name('offices');
            Route::post('offices', 'Precinct\PrecinctController@storeOffice');
            Route::put('offices/{id}', 'Precinct\PrecinctController@updateOffice');
            Route::delete('offices/{id}', 'Precinct\PrecinctController@deleteOffice')->name('offices.delete');

            Route::post('groups/{id}/send', 'Precinct\PrecinctController@sendPackages')->name('send-packages');
            Route::post('groups/{id}/accept', 'Precinct\PrecinctController@acceptPackages')->name('accept-packages');
            Route::post('groups/{id}/print', 'Precinct\PrecinctController@print')->name('print');
            Route::post('groups/{id}/delete', 'Precinct\PrecinctController@destroyGroup')->name('delete_group');

            Route::post('groups/{id}/packages', 'Precinct\PrecinctController@store')->name('store');

            Route::get('delete-package/{id}', 'Precinct\PrecinctController@deletePackage')->name('delete-package');
        });
        /* End Precinct Routes*/

        /* Start Surat Routes*/
        Route::group(['prefix' => '/surat', 'as' => 'surat.'], function () {
            Route::get('/', 'Surat\SuratController@index')->name('index');
            Route::get('/packages', 'Surat\SuratController@index')->name('packages');

            Route::get('containers', 'Surat\SuratController@containers')->name('containers');
            Route::post('containers', 'Surat\SuratController@storeContainer');

            Route::get('containers/{id}', 'Surat\SuratController@editContainer')->name('containers.edit');
            Route::post('containers/{id}/delete', 'Surat\SuratController@deleteContainer')->name('delete_group');

            Route::post('containers/{id}/send', 'Surat\SuratController@sendPackages')->name('send-packages');
            Route::post('containers/{id}/print', 'Surat\SuratController@print')->name('print');

            Route::get('offices', 'Surat\SuratOfficeController@offices')->name('offices');
            Route::post('offices', 'Surat\SuratOfficeController@storeOffice');
            Route::put('offices/{id}', 'Surat\SuratOfficeController@updateOffice')->name('offices.update');
            Route::delete('offices/{id}', 'Surat\SuratOfficeController@deleteOffice')->name('offices.delete');

            Route::post('/update-name', 'Surat\SuratController@updateContainer')->name('update_name');
            Route::post('groups/{id}/packages', 'Surat\SuratController@store')->name('store');

            Route::get('delete-package/{id}', 'Surat\SuratController@deletePackage')->name('delete-package');
        });
        /* End Surat Routes*/


        /* Start Courier-Saas Routes*/
        Route::group(['prefix' => '/courier-saas', 'as' => 'courier-saas.'], function () {
            Route::get('/', 'CourierSaas\CourierSaasController@index')->name('index');
            Route::get('/packages', 'CourierSaas\CourierSaasController@index')->name('packages');

            Route::get('containers', 'CourierSaas\CourierSaasController@containers')->name('containers');
            Route::post('containers', 'CourierSaas\CourierSaasController@storeContainer');

            Route::get('containers/{id}', 'CourierSaas\CourierSaasController@editContainer')->name('containers.edit');
            Route::post('containers/{id}/delete', 'CourierSaas\CourierSaasController@deleteContainer')->name('delete_group');

            Route::post('containers/{id}/send', 'CourierSaas\CourierSaasController@sendPackages')->name('send-packages');
            Route::post('containers/{id}/print', 'CourierSaas\CourierSaasController@print')->name('print');

            Route::get('offices', 'CourierSaas\CourierSaasOfficeController@offices')->name('offices');
            Route::post('offices', 'CourierSaas\CourierSaasOfficeController@storeOffice');
            Route::put('offices/{id}', 'CourierSaas\CourierSaasOfficeController@updateOffice')->name('offices.update');
            Route::delete('offices/{id}', 'CourierSaas\CourierSaasOfficeController@deleteOffice')->name('offices.delete');

            Route::post('/update-name', 'CourierSaas\CourierSaasController@updateContainer')->name('update_name');
            Route::post('groups/{id}/packages', 'CourierSaas\CourierSaasController@store')->name('store');

            Route::get('delete-package/{id}', 'CourierSaas\CourierSaasController@deletePackage')->name('delete-package');
        });
        /* End Courier-Saas Routes*/

        /* Default resources */
        $resources = [
            'career',
            'faq',
            'role',
            'application',
            'setting',
            'admin',
            'courier',
            'courier_delivery' => [
                ['name' => 'ajax', 'method' => 'post'],
                ['name' => 'info', 'method' => 'get'],
                ['name' => 'logs', 'method' => 'get'],
            ],
            'ue_ticket' => [
                ['name' => 'close', 'method' => 'get'],
            ],
            'ue_ticket_conversation',
            'ue_checkup' => [
                ['name' => 'close', 'method' => 'get'],
                ['name' => 'refresh', 'method' => 'get'],
            ],
            'newtype',
            'page',
            'news',
            'package_type',
            'category',
            'country',
            'warehouse',
            'user' => [
                ['name' => 'logs', 'method' => 'get'],
            ],
            'store',
            'city',
            'product',
            'package_carrier' => [
                ['name' => 'depesh', 'method' => 'get'],
                ['name' => 'regnumber', 'method' => 'get'],
            ],
            'air_package',
            'coupon',
            'order' => [
                ['name' => 'links', 'method' => 'get'],
                ['name' => 'linkajax', 'method' => 'post'],
                ['name' => 'ajax', 'method' => 'post'],
            ],
            'package' => [
                ['name' => 'request', 'method' => 'get'],
                ['name' => 'logs', 'method' => 'get'],
//                ['name' => 'resend_status', 'method' => 'post'],
                ['name' => 'label', 'method' => 'get'],
                ['name' => 'ue_info', 'method' => 'get'],
                ['name' => 'ue_user_update', 'method' => 'get'],
                ['name' => 'ue_weight_update', 'method' => 'get'],
                ['name' => 'ue_info', 'method' => 'post'],
                ['name' => 'carrier_update', 'method' => 'get'],
                ['name' => 'carrier_delete', 'method' => 'get'],
                ['name' => 'ajax', 'method' => 'post'],
                ['name' => 'bagcarrierupdate', 'method' => 'get'],
                ['name' => 'bagdepeshcheck', 'method' => 'get'],
                ['name' => 'bagdepesh', 'method' => 'get'],
            ],
            'cell' => [
                ['name' => 'ajax', 'method' => 'post'],
            ],
            'bag' => [
                ['name' => 'ajax', 'method' => 'post'],
            ],
            'done' => [
                ['name' => 'label', 'method' => 'get'],
            ],
            'unknown' => [
                ['name' => 'rescan', 'method' => 'get'],
            ],
            'paid',
            'logic_sync',
            'activity',
            'slider',
            'sms',
            'whatsapp',
            'mobile',
            'discount',
            'promo',
            'promo_log',
            'tariff',
            'weight_price',
            'courier_area',
            'courier_track',
            'in_customs_track',
            'status_track',
            'contact',
            'email',
            'gift_card' => [
                ['name' => 'label', 'method' => 'get'],
            ],
            'transaction',
            'campaign',
            'track' => [
                ['name' => 'request', 'method' => 'get'],
                ['name' => 'auto_courier', 'method' => 'get'],
                ['name' => 'auto_filial', 'method' => 'get'],
                ['name' => 'ajax', 'method' => 'post'],
                ['name' => 'logs', 'method' => 'get'],
                ['name' => 'resend_status', 'method' => 'get'],
                ['name' => 'depesh_check', 'method' => 'get'],
                ['name' => 'carrier_update', 'method' => 'get'],
                ['name' => 'carrier_delete', 'method' => 'get'],
                ['name' => 'bagcarrierupdate', 'method' => 'get'],
                ['name' => 'bagdepeshcheck', 'method' => 'get'],
                ['name' => 'bagdepesh', 'method' => 'get'],
                ['name' => 'label', 'method' => 'get'],
                ['name' => 'track_filial', 'method' => 'post'],
            ],
            'customer' => [
                ['name' => 'logs', 'method' => 'get'],
            ],
            'container' => [
                ['name' => 'sent', 'method' => 'get'],
                ['name' => 'depesh_start', 'method' => 'get'],
                ['name' => 'depesh_stop', 'method' => 'get'],
                ['name' => 'containerCustomsClearance', 'method' => 'get'],
                ['name' => 'containerCustomsCompleted', 'method' => 'get'],
                ['name' => 'containerIncreaseWeights', 'method' => 'get'],
                ['name' => 'airboxCustomsClearance', 'method' => 'get'],
                ['name' => 'airboxCustomsCompleted', 'method' => 'get'],
                ['name' => 'trackCustomsClearance', 'method' => 'get'],
                ['name' => 'trackCustomsCompleted', 'method' => 'get'],
                ['name' => 'create', 'method' => 'get'],
                ['name' => 'update_name', 'method' => 'post'],
                ['name' => 'update_mawb', 'method' => 'post'],
            ],
            'delivery_point',
            'filial',
            'notification',
        ];

        foreach ($resources as $key => $resource) {

            $route = is_array($resource) ? $key : $resource;

            $pluralRoute = str_plural($route);
            Route::resource($pluralRoute, studly_case($route) . 'Controller', [
                'parameters' => [
                    $pluralRoute => 'id',
                ],
            ]);

            /* Extra route for resource */
            if (is_array($resource)) {
                foreach ($resource as $singleRoute) {

                    if (is_array($singleRoute['name'])) {
                        $data = $singleRoute['name'];
                    } else {
                        $data = [
                            'url' => $pluralRoute . '/{id}/' . $singleRoute['name'],
                            'as' => $pluralRoute . "." . $singleRoute['name'],
                            'uses' => studly_case($route) . 'Controller@' . $singleRoute['name'],
                        ];
                    }

                    Route::{$singleRoute['method']}($data['url'], [
                        'as' => $data['as'],
                        'uses' => $data['uses'],
                    ]);
                }
            }
        }

        Route::resource('warehouses/{warehouse_id}/addresses', 'AddressController', [
            'parameters' => [
                'addresses' => 'id',
            ],
        ]);

        Route::resource('warehouses/{warehouse_id}/workers', 'WorkerController', [
            'parameters' => [
                'workers' => 'id',
            ],
        ]);

        Route::resource('tariffs/{tariff_id}/tariff_weights', 'TariffWeightController', [
            'parameters' => [
                'tariff_weights' => 'id',
            ],
        ]);

        Route::get('containers/search', 'ContainerController@search');
        Route::post('containers/store', 'ContainerController@createContainer')->name('containers.store');

        /*        Route::resource('couriers/{courier_id}/courier_areas', 'CourierAreaController', [
                    'parameters' => [
                        'courier_areas' => 'id',
                    ],
            ]);*/

        //Route::resource('tariffs/{tariff_id}/tariff_weights/{tariff_weight_id}/tariff_prices', 'TariffPriceController', [
        Route::resource('tariff_weights/{tariff_weight_id}/tariff_prices', 'TariffPriceController', [
            'parameters' => [
                'tariff_prices' => 'id',
            ],
        ]);

        Route::resource('ue_tickets/{ue_ticket_id}/ue_ticket_conversations', 'UeTicketConversationController', [
            'parameters' => [
                'ue_ticket_conversations' => 'id',
            ],
        ]);

        Route::get('barcode/{code?}', [
            'as' => 'admin.barcode.scan',
            'uses' => 'PackageController@barcodeScan',
        ])->where('code', '.*');

        Route::get('barcode/test/{code?}', [
            'as' => 'admin.barcode.scanTest',
            'uses' => 'PackageController@barcodeScanTest',
        ]);

        Route::get('courier_tracks/barcode/{code?}', [
            'as' => 'courier_tracks.scan',
            'uses' => 'CourierTrackController@barcodeScan',
        ]);

        Route::get('packages/parselcarrierlist/{id?}', [
            'as' => 'packages.parselcarrierlist',
            'uses' => 'PackageController@parselcarrierlist',
        ]);

        Route::get('packages/parselcarrierupdate/{id?}', [
            'as' => 'packages.parselcarrierupdate',
            'uses' => 'PackageController@parselcarrierupdate',
        ]);

        Route::get('packages/parseldepeshcheck/{id?}', [
            'as' => 'packages.parseldepeshcheck',
            'uses' => 'PackageController@parseldepeshcheck',
        ]);

        Route::get('packages/parseldepesh/{id?}', [
            'as' => 'packages.parseldepesh',
            'uses' => 'PackageController@parseldepesh',
        ]);

        Route::get('packages/parselukrexpress/{id?}', [
            'as' => 'packages.parselukrexpress',
            'uses' => 'PackageController@parselukrexpress',
        ]);

        Route::get('packages/packagedepeshcheck/{id?}', [
            'as' => 'packages.packagedepeshcheck',
            'uses' => 'PackageController@packagedepeshcheck',
        ]);

        Route::get('package_carriers/packagecanceldepesh/{id?}', [
            'as' => 'package_carriers.packagecanceldepesh',
            'uses' => 'PackageCarrierController@packagecanceldepesh',
        ]);

        Route::post('packages/multiple', ['as' => 'packages.multiple', 'uses' => 'PackageController@multiUpdate',]);


        Route::get('packages/packagecanceldepesh/{id?}', [
            'as' => 'packages.packagecanceldepesh',
            'uses' => 'PackageController@packagecanceldepesh',
        ]);

        Route::get('packages/packagedepesh/{id?}', [
            'as' => 'packages.packagedepesh',
            'uses' => 'PackageController@packagedepesh',
        ]);

        Route::get('packages/parselcanceldepesh/{id?}', [
            'as' => 'packages.parselcanceldepesh',
            'uses' => 'PackageController@parselcanceldepesh',
        ]);

        Route::post('packages/changeStatusAllDone', [
            'as' => 'packages.changeStatusAllDone',
            'uses' => 'PackageController@changeStatusAllDone',
        ]);


        Route::get('tracks/parselcarrierlist/{id?}', [
            'as' => 'tracks.parselcarrierlist',
            'uses' => 'TrackController@parselcarrierlist',
        ]);


        Route::get('tracks/states/{id?}', [
            'as' => 'tracks.states',
            'uses' => 'TrackController@statesList',
        ]);
        Route::post('tracks/states/{id?}', [
            'as' => 'tracks.states.post',
            'uses' => 'TrackController@statesPost',
        ]);

        Route::get('tracks/parselcarrierupdate/{id?}', [
            'as' => 'tracks.parselcarrierupdate',
            'uses' => 'TrackController@parselcarrierupdate',
        ]);

        Route::get('tracks/parseldepeshcheck/{id?}', [
            'as' => 'tracks.parseldepeshcheck',
            'uses' => 'TrackController@parseldepeshcheck',
        ]);

        Route::get('tracks/parseldepesh/{id?}', [
            'as' => 'tracks.parseldepesh',
            'uses' => 'TrackController@parseldepesh',
        ]);

        Route::get('tracks/packagedepeshcheck/{id?}', [
            'as' => 'tracks.packagedepeshcheck',
            'uses' => 'TrackController@packagedepeshcheck',
        ]);

        Route::get('tracks/packagecanceldepesh/{id?}', [
            'as' => 'tracks.packagecanceldepesh',
            'uses' => 'TrackController@packagecanceldepesh',
        ]);

        Route::get('tracks/packagedepesh/{id?}', [
            'as' => 'tracks.packagedepesh',
            'uses' => 'TrackController@packagedepesh',
        ]);


        Route::get('package_carriers/packagedepeshcheck/{id?}', [
            'as' => 'package_carriers.packagedepeshcheck',
            'uses' => 'PackageCarrierController@packagedepeshcheck',
        ]);

        /*Route::get('packages/depesh/{id?}' , function ($id) {
            $exitCode = Artisan::call('depesh',['parcel_id'=>$id]);
                return dd(Artisan::output());
        });*/

        Route::get('packages/export/{items?}', [
            'as' => 'packages.export',
            'uses' => 'PackageController@export',
        ]);

        Route::get('promo_logs/export/{items?}', [
            'as' => 'promo_logs.export',
            'uses' => 'PromoLogController@export',
        ]);

        Route::get('users/export/{items?}', [
            'as' => 'users.export',
            'uses' => 'UserController@export',
        ]);

        Route::get('packages/manifest/{items?}', [
            'as' => 'packages.manifest',
            'uses' => 'PackageController@manifest',
        ]);
    });

    Route::get('/sms-messages', [
        'as' => 'sms-messages.index',
        'uses' => 'SmsController@smsMessages'
    ]);

    require 'auth.php';
});
