<?php

namespace Hedii\LaravelGelfLogger;

use Gelf\Logger;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Illuminate\Support\ServiceProvider;

class LaravelGelfLoggerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/gelf-logger.php' => config_path('gelf-logger.php')
        ], 'config');
    }

    /**
     * Register bindings in the container.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/gelf-logger.php', 'gelf-logger');

        $publisher = new Publisher(
            new UdpTransport(
                $this->getConfig('host'),
                $this->getConfig('port')
            )
        );

        $this->app->instance(GelfLogger::class, new Logger($publisher));

        $this->app->alias(GelfLogger::class, 'gelf-logger');

        $this->loadHelpers();
    }

    /**
     * An helper to get a value from the config array.
     *
     * @param string $key
     * @return mixed
     */
    private function getConfig($key)
    {
        return $this->app['config']->get('gelf-logger')[$key];
    }

    /**
     * Include the helpers file.
     */
    private function loadHelpers()
    {
        include __DIR__ . '/helpers.php';
    }
}