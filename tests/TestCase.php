<?php

declare(strict_types=1);

namespace Nuwber\HypervelGelfLogger\Tests;

use Exception;
use Nuwber\HypervelGelfLogger\GelfLoggerFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    protected ContainerInterface $container;
    protected array $config = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->with('env')->willReturn('testing');

        $this->config = [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'level' => 'notice',
            'name' => 'my-custom-name',
            'host' => '127.0.0.2',
            'port' => 12202,
            'ignore_error' => false,
        ];
    }

    protected function createLogger(array $config = []): \Monolog\Logger
    {
        $factory = new GelfLoggerFactory($this->container);
        return $factory(array_merge($this->config, $config));
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

}
