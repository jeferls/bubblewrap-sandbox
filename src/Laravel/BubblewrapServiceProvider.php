<?php

namespace Greenn\Libs\Laravel;

use Greenn\Libs\BubblewrapSandbox;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel service provider that binds the BubblewrapSandbox into the container.
 */
class BubblewrapServiceProvider extends ServiceProvider
{
    /**
     * Register bindings.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/sandbox.php', 'sandbox');

        $this->app->singleton(BubblewrapSandbox::class, function ($app) {
            $config = $app['config']->get('sandbox', array());

            return BubblewrapSandbox::fromConfig($config);
        });

        $this->app->alias(BubblewrapSandbox::class, 'sandbox.bwrap');
    }

    /**
     * Publish config for Laravel apps.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes(array(
                __DIR__ . '/../../config/sandbox.php' => config_path('sandbox.php'),
            ), 'sandbox-config');
        }
    }
}
