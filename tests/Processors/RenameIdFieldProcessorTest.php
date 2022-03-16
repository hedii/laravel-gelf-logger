<?php

namespace Hedii\LaravelGelfLogger\Tests\Processors;

use Hedii\LaravelGelfLogger\Processors\RenameIdFieldProcessor;
use PHPUnit\Framework\TestCase;

class RenameIdFieldProcessorTest extends TestCase
{
    /** @test */
    public function it_should_rename_id_field(): void
    {
        $payload = [
            'context' => ['id' => 'bar'],
        ];

        $processor = new RenameIdFieldProcessor();

        $this->assertSame(['context' => ['_id' => 'bar']], $processor($payload));
    }
}
