<?php

namespace Abe27\BitkubService;

use Illuminate\Support\ServiceProvider;

class BitkubServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/bitkub.php',
            'bitkub'
        );

        $this->app->singleton('bitkub', function ($app) {
            return new BitkubService($app['config']['bitkub']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/bitkub.php' => config_path('bitkub.php'),
        ], 'bitkub-config');
    }
}
