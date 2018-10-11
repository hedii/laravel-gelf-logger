<?php

namespace Hedii\LaravelGelfLogger;

use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\UdpTransport;
use InvalidArgumentException;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Logger;

class GelfLoggerFactory
{
    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug' => Logger::DEBUG,
        'info' => Logger::INFO,
        'notice' => Logger::NOTICE,
        'warning' => Logger::WARNING,
        'error' => Logger::ERROR,
        'critical' => Logger::CRITICAL,
        'alert' => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

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
     * Parse the string level into a Monolog constant.
     *
     * @param array $config
     * @return int
     * @throws \InvalidArgumentException
     */
    protected function level(array $config): int
    {
        $level = $config['level'] ?? 'debug';

        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }

    /**
     * Extract the log channel from the given configuration.
     *
     * @param array $config
     * @return string
     */
    protected function parseChannel(array $config): string
    {
        if (! isset($config['name'])) {
            return $this->getFallbackChannelName();
        }

        return $config['name'];
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
