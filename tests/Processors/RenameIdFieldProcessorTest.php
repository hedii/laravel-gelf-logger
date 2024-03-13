<?php

namespace Hedii\LaravelGelfLogger\Tests\Processors;

use DateTimeImmutable;
use Hedii\LaravelGelfLogger\Processors\RenameIdFieldProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RenameIdFieldProcessorTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function it_should_rename_id_field(array $payloadContext, array $expected): void
    {
        $payload = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'gelf',
            level: Level::Debug,
            message: 'message',
            context: $payloadContext
        );

        $processor = new RenameIdFieldProcessor();

        $this->assertSame($expected, $processor($payload)->context);
    }

    public static function dataProvider(): array
    {
        return [
            'having neither underscore id nor id' => [
                ['someotherfield' => 'someothervalue'],
                ['someotherfield' => 'someothervalue'],
            ],
            'having id and underscore id' => [
                ['id' => 'bar', '_id' => 'bar2'],
                ['_id' => 'bar'],
            ],
            'having id and not underscore id' => [
                ['id' => 'bar'],
                ['_id' => 'bar'],
            ],
            'having no id and underscore id' => [
                ['_id' => 'bar', 'field1' => 'value1'],
                ['_id' => 'bar', 'field1' => 'value1'],
            ],
        ];
    }
}
