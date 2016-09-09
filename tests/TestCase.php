<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Hedii\LaravelGelfLogger\LaravelGelfLoggerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Load the command service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelGelfLoggerServiceProvider::class
        ];
    }
}