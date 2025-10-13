<?php
//Route::group(['middleware' => ['auth', 'email_verified'], 'prefix' => 'user'], function () {
Route::group(['middleware' => ['auth'], 'prefix' => 'user'], function () {

    Route::get('/', [
        'as' => 'addresses',
        'uses' => 'UserController@addresses'
    ]);

    Route::get('banned', [
        'as' => 'banned',
        'uses' => 'UserController@banned'
    ]);

    Route::get('cds/{id?}', [
        'as' => 'cds',
        'uses' => 'UserController@cds'
    ]);

    Route::get('cds/create', [
        'as' => 'cds.create',
        'uses' => 'UserController@createCD'
    ]);

    Route::post('cds/create', [
        'as' => 'cds.store',
        'uses' => 'UserController@storeCD'
    ]);

    Route::delete('cd/{id}', [
        'as' => 'cds.delete',
        'uses' => 'UserController@deleteCD'
    ]);

    Route::post('cancelcd/{id}', [
        'as' => 'cds.cancel',
        'uses' => 'UserController@cancelCD'
    ]);

    Route::post('restorecd/{id}', [
        'as' => 'cds.restore',
        'uses' => 'UserController@restoreCD'
    ]);

    Route::post('cd/{id}', [
        'as' => 'cds.update',
        'uses' => 'UserController@updateCD'
    ]);

    Route::get('editcd/{id}', [
        'as' => 'cds.edit',
        'uses' => 'UserController@editCD'
    ]);

    Route::get('paycd/{id?}', [
        'as' => 'cds.pay',
        'uses' => 'UserController@payCD'
    ]);

    Route::get('showcd/{id}', [
        'as' => 'cds.show',
        'uses' => 'UserController@showCD'
    ]);

    Route::get('orders', [
        'as' => 'my-orders',
        'uses' => 'UserController@orders'
    ]);

    Route::get('orders/create', [
        'as' => 'my-orders.create',
        'uses' => 'UserController@createOrder'
    ]);

    Route::post('orders/create', [
        'as' => 'my-orders.store',
        'uses' => 'UserController@storeOrder'
    ]);

    Route::delete('order/{id}', [
        'as' => 'my-orders.delete',
        'uses' => 'UserController@deleteOrder'
    ]);

    Route::delete('link/{id}', [
        'as' => 'my-orders.link.delete',
        'uses' => 'UserController@deleteLink'
    ]);

    Route::get('order/{id}', [
        'as' => 'my-orders.show',
        'uses' => 'UserController@order'
    ]);

    Route::get('packages/{id?}', [
        'as' => 'my-packages',
        'uses' => 'UserController@packages'
    ]);

    Route::get('declaration', [
        'as' => 'declaration.create',
        'uses' => 'UserController@declarationCreate',
    ]);
    Route::post('declaration', [
        'as' => 'declaration.store',
        'uses' => 'UserController@declarationStore',
    ]);

    Route::get('declaration/{id}/{page?}', [
        'as' => 'declaration.edit',
        'uses' => 'UserController@declaration',
    ]);
    Route::get('declaration/delete/{id}', [
        'as' => 'declaration.delete',
        'uses' => 'UserController@declarationDelete',
    ]);

    Route::post('declaration/{id}/{page?}', [
        'as' => 'declaration.update',
        'uses' => 'UserController@declarationUpdate',
    ]);

    Route::get('edit/{nulled?}', [
        'as' => 'edit',
        'uses' => 'UserController@edit'
    ]);
    Route::post('edit', [
        'as' => 'update',
        'uses' => 'UserController@update'
    ]);

    Route::post('/verification-query', 'UserController@query')->name('sms-verification-code');
});
