<?php

namespace Hedii\LaravelGelfLogger\Tests;

use DateTimeImmutable;
use Hedii\LaravelGelfLogger\GelfLoggerFactory;
use Illuminate\Support\Facades\Log;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;

class GelfMessageTest extends TestCase
{
    #[Test]
    public function it_should_append_prefixes(): void
    {
        $this->mergeConfig('logging.channels.gelf', [
            'system_name' => 'my-system-namex',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'context_prefix' => 'ctxt_',
            'extra_prefix' => 'extra_',
        ]);

        $formattedMessage = Log::channel('gelf')->getHandlers()[0]->getFormatter()->format(new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'gelf',
            level: Level::Debug,
            message: 'test',
            context: ['id' => '777', 'message' => 'custom'],
            extra: ['ip' => '127.0.0.1', 'source' => 'tests'],
        ));

        $this->assertArrayHasKey('extra_ip', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('extra_source', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('ctxt_id', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('ctxt_message', $formattedMessage->getAllAdditionals());
    }

    #[Test]
    public function it_should_not_append_prefixes(): void
    {
        $this->mergeConfig('logging.channels.gelf', [
            'system_name' => 'my-system-namex',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
        ]);

        $formattedMessage = Log::channel('gelf')->getHandlers()[0]->getFormatter()->format(new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'gelf',
            level: Level::Debug,
            message: 'test',
            context: ['id' => '777'],
            extra: ['ip' => '127.0.0.1'],
        ));

        $this->assertArrayHasKey('ip', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('id', $formattedMessage->getAllAdditionals());
    }

    #[Test]
    public function null_config_variables_should_not_add_prefixes(): void
    {
        $this->mergeConfig('logging.channels.gelf', [
            'system_name' => 'my-system-namex',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'context_prefix' => null,
            'extra_prefix' => null,
        ]);

        $formattedMessage = Log::channel('gelf')->getHandlers()[0]->getFormatter()->format(new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test_channel',
            level: Level::Debug,
            message: 'test',
            context: ['id' => '777'],
            extra: ['ip' => '127.0.0.1'],
        ));

        $this->assertArrayHasKey('ip', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('id', $formattedMessage->getAllAdditionals());
    }
}
