<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class SaySayServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        // - first the published/overwritten views (in case they have any changes)
        $this->loadViewsFrom(resource_path('views/vendor/saysay/crud'), 'crud');

        $this->loadTranslationsFrom(realpath(__DIR__ . '/resources/lang'), 'saysay');

        // PUBLISH FILES

        // publish lang files
        $this->publishes([__DIR__ . '/resources/lang' => resource_path('lang/vendor/saysay')], 'lang');

        // publish views
        $this->publishes([__DIR__ . '/resources/views' => resource_path('views/vendor/saysay/crud')], 'views');

        // publish config file
        $this->publishes([__DIR__ . '/config' => config_path()], 'config');

        // publish public saysay CRUD assets
        $this->publishes([__DIR__ . '/public' => public_path('vendor/saysay')], 'public');

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(config_path('saysay/crud.php'), 'saysay.crud');
    }
}
