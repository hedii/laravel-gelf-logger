<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Hedii\LaravelGelfLogger\GelfLoggerFactory;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase as Orchestra;

class GelfMessageTest extends Orchestra
{
    /** @test */
    public function it_should_append_prefixes(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'system_name' => 'my-system-namex',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'context_prefix' => 'ctxt_',
            'extra_prefix' => 'extra_',
        ]);

        $formattedMessage = Log::channel('gelf')->getHandlers()[0]->getFormatter()->format([
            'datetime' => '1591097093.0',
            'message' => 'test',
            'level' => 100,
            'extra' => ['ip' => '127.0.0.1', 'source' => 'tests'],
            'context' => ['id' => '777', 'message' => 'custom'],
        ]);

        $this->assertArrayHasKey('extra_ip', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('extra_source', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('ctxt_id', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('ctxt_message', $formattedMessage->getAllAdditionals());
    }

    /** @test */
    public function it_should_not_append_prefixes(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'system_name' => 'my-system-namex',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
        ]);

        $formattedMessage = Log::channel('gelf')->getHandlers()[0]->getFormatter()->format([
            'datetime' => '1591097093.0',
            'message' => 'test',
            'level' => 100,
            'extra' => ['ip' => '127.0.0.1'],
            'context' => ['id' => '777'],
        ]);

        $this->assertArrayHasKey('ip', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('id', $formattedMessage->getAllAdditionals());
    }

    /** @test */
    public function null_config_variables_should_not_add_prefixes(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'system_name' => 'my-system-namex',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'context_prefix' => null,
            'extra_prefix' => null,
        ]);

        $formattedMessage = Log::channel('gelf')->getHandlers()[0]->getFormatter()->format([
            'datetime' => '1591097093.0',
            'message' => 'test',
            'level' => 100,
            'extra' => ['ip' => '127.0.0.1'],
            'context' => ['id' => '777'],
        ]);

        $this->assertArrayHasKey('ip', $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey('id', $formattedMessage->getAllAdditionals());
    }
}
