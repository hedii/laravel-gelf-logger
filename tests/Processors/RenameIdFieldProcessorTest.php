<?php

namespace Hedii\LaravelGelfLogger\Tests\Processors;

use DateTimeImmutable;
use Hedii\LaravelGelfLogger\Processors\RenameIdFieldProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class RenameIdFieldProcessorTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_should_rename_id_field(array $payloadContext, array $expected): void
    {
        $payload = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'gelf',
            level: Level::Debug,
            message: 'message',
            context: $payloadContext);

        $processor = new RenameIdFieldProcessor();

        $this->assertSame($expected, $processor($payload)->context);
    }

    public function dataProvider(): array
    {
        return [
            'have_neither_underscore_id_nor_id' => [
                ['someotherfield' => 'someothervalue'],
                ['someotherfield' => 'someothervalue']
            ],
            'having_id_and_underscore_id' => [
                ['id' => 'bar', '_id' => 'bar2'],
                ['_id' => 'bar']
            ],
            'having_id_and_not_underscore_id' => [
                ['id' => 'bar'],
                ['_id' => 'bar']
            ],
            'having_no_id_and_underscore_id' => [
                ['_id' => 'bar', 'field1' => 'value1'],
                ['_id' => 'bar', 'field1' => 'value1']
            ],
        ];
    }
}
