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
    public function it_should_rename_id_field(array $payloadContext, array $expected, string $fieldName = '_id'): void
    {
        $payload = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'gelf',
            level: Level::Debug,
            message: 'message',
            context: $payloadContext
        );

        $processor = new RenameIdFieldProcessor($fieldName);

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
            'having custom id filedName configuration' => [
                ['id' => 'bar'],
                ['_other_key' => 'bar'],
                '_other_key',
            ],
        ];
    }
}
