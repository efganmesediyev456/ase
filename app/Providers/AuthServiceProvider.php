<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('read-dashboard', function ($user) {
            return $user->hasPermission('read-dashboard');
            //return method_exists($user, 'hasPermission') && $user->hasPermission('read-dashboard');
        });
        Gate::define('customs-check', function ($user) {
            return $user->hasPermission('customs-check');
        });
        Gate::define('customs-reset', function ($user) {
            return $user->hasPermission('customs-reset');
        });
        Gate::define('customs-depesh', function ($user) {
            return $user->hasPermission('customs-depesh');
        });

        //
    }
}
