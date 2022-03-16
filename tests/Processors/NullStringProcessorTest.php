<?php

namespace Hedii\LaravelGelfLogger\Tests\Processors;

use Hedii\LaravelGelfLogger\Processors\NullStringProcessor;
use PHPUnit\Framework\TestCase;

class NullStringProcessorTest extends TestCase
{
    /** @test */
    public function it_should_transform_null_string_to_null(): void
    {
        $payload = [
            'context' => [
                'key1' => 'bar',
                'key2' => 'NULL',
                'key3' => 'null',
                'key4' => null,
            ],
        ];

        $processor = new NullStringProcessor();

        $this->assertSame([
            'context' => [
                'key1' => 'bar',
                'key2' => null,
                'key3' => null,
                'key4' => null,
            ],
        ], $processor($payload));
    }
}
