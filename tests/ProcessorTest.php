<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Hedii\LaravelGelfLogger\Tests\Fake\AnotherTestProcessor;
use Hedii\LaravelGelfLogger\Tests\Fake\TestProcessor;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;

class ProcessorTest extends TestCase
{
    #[Test]
    public function it_should_have_the_configured_processors(): void
    {
        $this->mergeConfig('logging.channels.gelf', [
            'processors' => [TestProcessor::class, AnotherTestProcessor::class],
        ]);

        $logger = Log::channel('gelf');
        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(AnotherTestProcessor::class, $handler->popProcessor());
        $this->assertInstanceOf(TestProcessor::class, $handler->popProcessor());
    }
}
