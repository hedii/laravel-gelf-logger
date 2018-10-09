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
        /** @var \Illuminate\Log\Logger $handler */
        $logger = Log::channel('gelf');

        $this->assertInstanceOf(Logger::class, $logger->getLogger());
        $this->assertSame($logger->getName(), 'my-custom-name');
        $this->assertCount(1, $logger->getHandlers());

        /** @var \Monolog\Handler\GelfHandler $handler */
        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(GelfHandler::class, $handler);
        $this->assertSame(Logger::NOTICE, $handler->getLevel());
        $this->assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

        // cannot test publisher and transport... :(
    }
}
