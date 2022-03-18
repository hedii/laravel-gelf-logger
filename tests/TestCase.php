<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Exception;
use Hedii\LaravelGelfLogger\GelfLoggerFactory;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;

class TestCase extends Orchestra
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('logging.default', 'gelf');
        $app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'level' => 'notice',
            'name' => 'my-custom-name',
            'host' => '127.0.0.2',
            'port' => 12202,
            'ignore_error' => false,
        ]);
    }

    /**
     * Get protected or private attribute from an object.
     *
     * @throws \Exception
     */
    protected function getAttribute(object $object, string $property): mixed
    {
        try {
            $reflector = new ReflectionClass($object);
            $attribute = $reflector->getProperty($property);
            $attribute->setAccessible(true);

            return $attribute->getValue($object);
        } catch (Exception) {
            throw new Exception('Cannot get attribute from the provided object');
        }
    }

    /**
     * Get protected or private constant from a class.
     *
     * @throws \Exception
     */
    protected function getConstant(string $class, string $constant): mixed
    {
        try {
            $reflector = new ReflectionClass($class);

            return $reflector->getConstant($constant);
        } catch (Exception) {
            throw new Exception('Cannot get attribute from the provided class');
        }
    }

    /**
     * Merge a given config to the global config.
     */
    protected function mergeConfig(string $key, array $values): void
    {
        $config = $this->app['config'];

        $config->set($key, array_merge($config->get($key), $values));
    }
}
