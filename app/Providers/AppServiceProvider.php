<?php

namespace App\Providers;

use App\Services\AzeriExpress\AuthService;
use App\Services\AzeriExpress\CourierService;
use App\Services\HttpClient;
use Blade;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);


        Blade::directive('checking', function ($expression) {

            if (auth()->guard('admin')->check()) {
                return "<?php if (app('laratrust')->can({$expression})): ?>";
            } else {
                return ('<?php if (isset($_can[explode("-", ' . $expression . ')[0]]) && $_can[explode("-", ' . $expression . ')[0]]): ?>');
            }
        });

        Blade::directive('endchecking', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->alias('bugsnag.logger', Log::class);
//        $this->app->alias('bugsnag.logger', LoggerInterface::class);

        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService($app->make(HttpClient::class));
        });

        $this->app->singleton(CourierService::class, function ($app) {
            return new CourierService($app->make(HttpClient::class),$app->make(AuthService::class));
        });
    }
}
