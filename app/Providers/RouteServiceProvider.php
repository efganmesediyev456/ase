<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        \Route::pattern('id', '\d+');
        \Route::pattern('slug', '[a-z0-9-]+');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapUnitradeRoutes();
        $this->mapIntegrationRoutes();

        $this->mapAdminRoutes();

        $this->mapWarehouseRoutes();
        $this->mapMeestRoutes();
        $this->mapCdRoutes();

        $this->mapFrontRoutes();
    }

    protected function mapAdminRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/admin.php'));
    }

    protected function mapWareHouseRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/warehouse.php'));
    }

    protected function mapFrontRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/front.php'));
    }

    protected function mapCdRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/cd.php'));
    }

    protected function mapApiRoutes()
    {
        Route::middleware(['logger'])
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
    protected function mapUnitradeRoutes()
    {
        Route::middleware(['logger'])
            ->namespace('App\Http\Controllers\Api\Unitrade')
            ->group(base_path('routes/api/unitrade.php'));
    }
    protected function mapIntegrationRoutes()
    {
        Route::middleware(['logger'])
            ->namespace('App\Http\Controllers\Api\Integration')
            ->group(base_path('routes/api/integration.php'));
    }

    protected function mapMeestRoutes()
    {
        Route::middleware(['logger'])
            ->namespace('App\Http\Controllers\Api\Meest')
            ->group(base_path('routes/api/meest.php'));
    }
}
