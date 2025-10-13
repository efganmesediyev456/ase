<?php
/* Auth */
Route::get('login', ['as' => 'login', 'uses' => 'Auth\LoginController@showLoginForm']);
Route::post('login', ['as' => 'auth.login', 'uses' => 'Auth\LoginController@login']);
Route::post('logout', ['as' => 'auth.logout', 'uses' => 'Auth\LoginController@logout']);
Route::get('logout', ['as' => 'auth.get.logout', 'uses' => 'Auth\LoginController@logout']);

// Password Reset Routes...
Route::get('password/reset/{token?}', ['as' => 'auth.password.reset',
    'uses' => 'Auth\ResetPasswordController@showResetForm',
]);
Route::get('password/email', ['as' => 'auth.password.email',
    'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm',
]);
Route::post('password/email', ['as' => 'auth.password.email',
    'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail',
]);

Route::post('password/reset', ['as' => 'auth.password.reset', 'uses' => 'Auth\ResetPasswordController@reset']);
