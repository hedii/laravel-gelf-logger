<?php

namespace Hedii\LaravelGelfLogger;

use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\UdpTransport;
use Illuminate\Log\ParsesLogConfiguration;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Logger;

class GelfLoggerFactory
{
    use ParsesLogConfiguration;

    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config): Logger
    {
        $transport = new IgnoreErrorTransportWrapper(
            new UdpTransport(
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 12201
            )
        );

        $handler = new GelfHandler(new Publisher($transport), $this->level($config));

        $handler->setFormatter(new GelfMessageFormatter(null, null, null));

        return new Logger($this->parseChannel($config), [$handler]);
    }

    /**
     * Get fallback log channel name.
     *
     * @return string
     */
    protected function getFallbackChannelName(): string
    {
        return app()->bound('env') ? app()->environment() : 'production';
    }
}
