<?php

namespace Hedii\LaravelGelfLogger\Tests\Processors;

use DateTimeImmutable;
use Hedii\LaravelGelfLogger\Processors\NullStringProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NullStringProcessorTest extends TestCase
{
    #[Test]
    public function it_should_transform_null_string_to_null(): void
    {
        $payload = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'gelf',
            level: Level::Debug,
            message: 'message',
            context: [
                'key1' => 'bar',
                'key2' => 'NULL',
                'key3' => 'null',
                'key4' => null,
            ]
        );

        $processor = new NullStringProcessor();

        $this->assertSame([
            'key1' => 'bar',
            'key2' => null,
            'key3' => null,
            'key4' => null,
        ], $processor($payload)->context);
    }
}
