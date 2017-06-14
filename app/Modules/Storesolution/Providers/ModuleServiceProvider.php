<?php

namespace App\Modules\Storesolution\Providers;

use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'storesolution');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'storesolution');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'storesolution');
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
