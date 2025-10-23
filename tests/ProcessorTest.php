<?php

declare(strict_types=1);

namespace Nuwber\HypervelGelfLogger\Tests;

use Nuwber\HypervelGelfLogger\Tests\Fake\AnotherTestProcessor;
use Nuwber\HypervelGelfLogger\Tests\Fake\TestProcessor;
use PHPUnit\Framework\Attributes\Test;

class ProcessorTest extends TestCase
{
    #[Test]
    public function it_should_have_the_configured_processors(): void
    {
        $logger = $this->createLogger([
            'processors' => [TestProcessor::class, AnotherTestProcessor::class],
        ]);
        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(AnotherTestProcessor::class, $handler->popProcessor());
        $this->assertInstanceOf(TestProcessor::class, $handler->popProcessor());
    }
}
