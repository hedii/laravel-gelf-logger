<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Hedii\LaravelGelfLogger\GelfLoggerFactory;
use Hedii\LaravelGelfLogger\Tests\Fake\AnotherTestProcessor;
use Hedii\LaravelGelfLogger\Tests\Fake\TestProcessor;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase as Orchestra;

class ProcessorTest extends Orchestra
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
            'processors' => [TestProcessor::class, AnotherTestProcessor::class],
        ]);
    }

    /** @test */
    public function it_should_have_the_configured_processors(): void
    {
        $logger = Log::channel('gelf');
        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(AnotherTestProcessor::class, $handler->popProcessor());
        $this->assertInstanceOf(TestProcessor::class, $handler->popProcessor());
    }
}
