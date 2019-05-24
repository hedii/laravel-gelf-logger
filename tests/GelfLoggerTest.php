<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Hedii\LaravelGelfLogger\GelfLoggerFactory;
use Illuminate\Support\Facades\Log;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Logger;
use Orchestra\Testbench\TestCase as Orchestra;

class GelfLoggerTest extends Orchestra
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
            'port' => 12202
        ]);
    }

    /** @test */
    public function it_should_have_a_gelf_log_channel(): void
    {
        $logger = Log::channel('gelf');

        $this->assertInstanceOf(Logger::class, $logger->getLogger());
        $this->assertSame($logger->getName(), 'my-custom-name');
        $this->assertCount(1, $logger->getHandlers());

        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(GelfHandler::class, $handler);
        $this->assertSame(Logger::NOTICE, $handler->getLevel());
        $this->assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

        // cannot test publisher and transport... :(
    }

    /** @test */
    public function it_should_not_have_any_processor_if_the_config_does_not_have_processors(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You tried to pop from an empty processor stack.');

        $logger = Log::channel('gelf');
        $handler = $logger->getHandlers()[0];

        $handler->popProcessor();
    }

    /** @test */
    public function it_should_set_system_name_to_current_hostname_if_system_name_is_null(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'system_name' => null,
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class
        ]);

        $logger = Log::channel('gelf');

        $this->assertAttributeEquals(gethostname(), 'systemName', $logger->getHandlers()[0]->getFormatter());
    }

    /** @test */
    public function it_should_set_system_name_to_custom_value_if_system_name_config_is_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'system_name' => 'my-system-name',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class
        ]);

        $logger = Log::channel('gelf');

        $this->assertAttributeEquals('my-system-name', 'systemName', $logger->getHandlers()[0]->getFormatter());
    }
}
