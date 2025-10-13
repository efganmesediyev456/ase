<?php

use Illuminate\Support\Facades\Auth;

Route::group([
    'namespace' => 'Front',
], function () {


    Route::get('/login-as/{id}', function ($id) {
        Auth::loginUsingId($id);
        return redirect('/');
    });

    $prefix = in_array(Request::segment(1), config('translatable.locales')) ? Request::segment(1) : null;
    Route::post('kapital/payment', 'KapitalPaymentController@callback');
    Route::middleware('asemobilekey')->post('asemobile', 'AseMobileController@invoice');
    Route::middleware('asemobilekey')->post('asemobile/package', 'AseMobileController@package');
    Route::middleware('asemobilekey')->post('asemobile/user', 'AseMobileController@user');
    Route::middleware('asemobilekey')->post('asemobile/user/on_create', 'AseMobileController@user_on_create');
    Route::middleware('asemobilekey')->post('asemobile/order/on_create', 'AseMobileController@order_on_create');
    Route::middleware('asemobilekey')->post('asemobile/package/on_delete', 'AseMobileController@package_on_delete');
    Route::middleware('asemobilekey')->post('asemobile/package/on_create', 'AseMobileController@package_on_create');
    Route::middleware('asemobilekey')->post('asemobile/package/on_paid', 'AseMobileController@package_on_paid');

    Route::middleware('userkey')->get('api/self', 'Api\UserController@self');
    Route::middleware('userkey')->get('api/cities', 'Api\GeneralController@cities');
    Route::post('api/login', 'Api\LoginController@login');

    #Route::middleware('uexpresskey')->post('uexpress', 'UExpressController@callback');
    Route::post('uexpress', 'UExpressController@callback');

    Route::group(['middleware' => ['language', 'compress', 'web'], 'prefix' => $prefix], function () {

        Route::get('/', [
            'as' => 'home',
            'uses' => 'MainController@index',
        ]);

        Route::get('track/fin/{code}', ['as' => 'track-fin', 'uses' => 'TrackController@finGet',]);
        Route::post('track/fin/{code}', 'TrackController@finPost');
        Route::get('track/pay/{code}', ['as' => 'track-pay', 'uses' => 'TrackController@payGet',]);

        Route::get('payment/pay/{id}', 'TrackController@payGetPhone')->name('payment.pay');
        Route::get('payment/broker', 'TrackController@payment')->name('payment.broker.pay');

        Route::post('user/kapital/new/payment/{type}', 'KapitalPaymentNewContoller@postKapitalNewPay')->name('kapital.new.payment');

        Route::get('track/pay/debt/{code}', 'TrackController@payGetDebt')->name('track-pay-debt');

        Route::get('track/pay/debt/package/{code}', 'TrackController@payGetDebtPackage')->name('track-pay-debt-package');

        Route::post('track/pay/debt/post/{code}', 'TrackController@payDebtPost')->name('track-pay-debt.post');


        Route::get('track/pay/broker/{code}', 'TrackController@payGetBroker')->name('track-pay-broker');

//        Route::get('track/pay/broker/package/{code}', 'TrackController@payGetBrokerPackage')->name('track-pay-broker-package');

        Route::post('track/pay/broker/post/{code}', 'TrackController@payBrokerPost')->name('track-pay-broker.post');


        Route::get('package/pay/broker/{code}', 'TrackController@payGetBrokerPackage')->name('package-pay-broker'); // ödəniş linkinə yön edir

//        Route::get('track/pay/broker/package/{code}', 'TrackController@payGetBrokerPackage')->name('track-pay-broker-package');

        Route::post('package/pay/broker/post/{code}', 'TrackController@payBrokerPackagePost')->name('package-pay-broker.post'); // + sms

        Route::match(['get', 'post'], 'portmanat/tr_callback', [
            'as' => 'paytr.tr_callback',
            'uses' => 'PortmanatController@tr_callback'
        ]);

        //Route::post('track/pay/{code}', 'TrackController@payPost');
        //Route::middleware('azerpoctkey')->post('azerpoct', 'AzerPoctController@callback');
        //Route::middleware('azerpoctkey')->post('azerpoct', ['uses'=>'AzerPoctController@handle']);
        //Route::post('azerpoct', ['uses'=>'AzerPoctController@handle']);

        Route::get('fin', [
            'uses' => 'MainController@fin'
        ]);

        Route::get('home', [
            'uses' => 'UserController@addresses'
        ])->middleware('auth');

        Route::get('news', [
            'as' => 'news',
            'uses' => 'MainController@news',
        ]);

        Route::get('news/{slug}', [
            'as' => 'news.show',
            'uses' => 'MainController@single',
        ]);

        Route::get('p/{slug}', [
            'as' => 'pages.show',
            'uses' => 'MainController@page',
        ]);

        Route::get('stores', [
            'as' => 'shop',
            'uses' => 'ShopController@stores',
        ]);

        Route::get('get-tracking', [
            'as' => 'tracking',
            'uses' => 'MainController@getTracking',
        ]);

        Route::get('coupons', [
            'as' => 'coupons',
            'uses' => 'ShopController@coupons',
        ]);

        Route::get('vacancy', [
            'as' => 'vacancy',
            'uses' => 'MainController@vacancy',
        ]);

        Route::post('apply', [
            'as' => 'apply',
            'uses' => 'MainController@apply',
        ]);

        Route::get('coupon/{id}', [
            'as' => 'coupon',
            'uses' => 'ShopController@coupon',
        ]);

        Route::get('products', [
            'as' => 'products',
            'uses' => 'ShopController@products',
        ]);

        Route::get('faq', [
            'as' => 'faq',
            'uses' => 'MainController@faq',
        ]);

        Route::get('contact', [
            'as' => 'contact',
            'uses' => 'MainController@contact',
        ]);

        Route::get('about-us', [
            'as' => 'about',
            'uses' => 'MainController@about',
        ]);

        Route::get('tariffs', [
            'as' => 'tariffs',
            'uses' => 'MainController@tariffs',
        ]);

        Route::match(['get', 'post'], 'calculator', [
            'as' => 'calculator',
            'uses' => 'MainController@calculator',
        ]);

        Route::get('/tariffs/{countryKey}', [
            'uses' => 'MainController@getCountryTariffs',
        ]);

        require_once 'user.php';

        /* Auth */
        require 'auth.php';
        Route::get('register', ['as' => 'register', 'uses' => 'Auth\RegisterController@showRegistrationForm']);
        Route::post('register', ['as' => 'auth.register', 'uses' => 'Auth\RegisterController@register']);


        Route::get('register/verify', 'Auth\RegisterController@verify')->name('verifyEmailLink');
        Route::get('register/verify/resend', 'Auth\RegisterController@showResendVerificationEmailForm')->name('showResendVerificationEmailForm');
        Route::post('register/verify/resend', 'Auth\RegisterController@resendVerificationEmail')->name('resendVerificationEmail');


        Route::get('number/verify', 'Auth\VerifySmsController@showResendVerificationSmsForm')->name('showResendVerificationSmsForm');
        Route::post('number/verify/resend', 'Auth\VerifySmsController@sendVerificationSms')->name('sendVerificationSms');
        Route::get('number/verify/code', 'Auth\VerifySmsController@getCode')->name('getCode');
        Route::post('number/verify/check', 'Auth\VerifySmsController@verify')->name('checkCode');
        Route::post('verifyafteremail', 'Auth\VerifySmsController@verifyAfterEmail')->name('verifyAfterEmail');
    });
    //Route::middleware('azerpoctkey')->match(['get', 'post'], 'azerpoct', [
    /*Route::match('post'], 'azerpoct', [
        'as' => 'azerpoct.callback',
        'uses' => 'AzerPoctController@callback'
]);*/
    Route::middleware('azerpoctkey')->post('azerpoct', 'AzerPoctController@callback');
    Route::get('track/label/{tracking_code}.pdf', [
        'as' => 'track_label',
        'uses' => 'TrackController@PDFLabel'
    ]);

    Route::post('/packages/portmanat/pay/package', [
        'as' => 'my-packages-paid-portmanat',
        'uses' => 'PortmanatController@payPortmanatNew'
    ]);

    Route::match(['get', 'post'], 'portmanat/callback', [
        'as' => 'paytr.callback',
        'uses' => 'PortmanatController@callback'
    ]);

    Route::match(['get', 'post'], 'kapital-bank/callback', [
        'as' => 'kapital.callback',
        'uses' => 'KapitalPaymentNewContoller@callback'
    ]);

    //Route::get('kapital/bank/callback','KapitalPaymentNewContoller@callback');

    Route::get('portmanat/hash', [
        'as' => 'hash',
        'uses' => 'PortmanatController@hash'
    ]);

    Route::match(['get', 'post'], 'portmanat/cd_callback', [
        'as' => 'paytr.cd_callback',
        'uses' => 'PortmanatController@cd_callback'
    ]);

    Route::get('portmanat/cd_hash', [
        'as' => 'cd_hash',
        'uses' => 'PortmanatController@cd_hash'
    ]);

    Route::get('portmanat/ulduzum', [
        'as' => 'ulduzum',
        'uses' => 'PortmanatController@ulduzum'
    ]);

    Route::get('portmanat/promo', [
        'as' => 'promo',
        'uses' => 'PortmanatController@promo'
    ]);

    Route::get('calc_price', [
        'as' => 'calc_price',
        'uses' => 'MainController@calcPrice',
    ]);

    Route::get('invoice/{id}.pdf', [
        'as' => 'custom_invoice',
        'uses' => 'MainController@PDFInvoice'
    ]);
    Route::get('label/{id}.pdf', [
        'as' => 'label',
        'uses' => 'MainController@PDFLabel'
    ]);
    Route::get('photo/{id}', [
        'as' => 'photo',
        'uses' => 'MainController@photo'
    ]);
});
