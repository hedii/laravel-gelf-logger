<?php

namespace Hedii\LaravelGelfLogger;

use Gelf\Publisher;
use Gelf\Transport\AbstractTransport;
use Gelf\Transport\HttpTransport;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\SslOptions;
use Gelf\Transport\TcpTransport;
use Gelf\Transport\UdpTransport;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Logger;

class GelfLoggerFactory
{
    protected $app;

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

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function __invoke(array $config): Logger
    {
        $transport = new IgnoreErrorTransportWrapper(
            $this->getTransport(
                $config['transport'] ?? 'udp',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 12201,
                $config['path'] ?? null,
                $this->enableSsl($config) ? $this->sslOptions($config['ssl_options'] ?? null) : null
            )
        );

        $handler = new GelfHandler(new Publisher($transport), $this->level($config));

        $handler->setFormatter(
            new GelfMessageFormatter(
                $config['system_name'] ?? null,
                $config['extra_prefix'] ?? null,
                $config['context_prefix'] ?? '',
                $config['max_length'] ?? null
            )
        );

        foreach ($this->parseProcessors($config) as $processor) {
            $handler->pushProcessor(new $processor);
        }

        return new Logger($this->parseChannel($config), [$handler]);
    }

    protected function getTransport(
        string $transport,
        string $host,
        int $port,
        ?string $path = null,
        ?SslOptions $sslOptions = null
    ): AbstractTransport {
        return match (strtolower($transport)) {
            'tcp' => new TcpTransport($host, $port, $sslOptions),
            'http' => new HttpTransport($host, $port, $path ?? HttpTransport::DEFAULT_PATH, $sslOptions),
            default => new UdpTransport($host, $port),
        };
    }

    protected function enableSsl(array $config): bool
    {
        if (! isset($config['transport']) || $config['transport'] === 'udp') {
            return false;
        }

        return $config['ssl'] ?? false;
    }

    protected function sslOptions(?array $sslConfig = null): SslOptions
    {
        $sslOptions = new SslOptions();

        if (! $sslConfig) {
            return $sslOptions;
        }

        $sslOptions->setVerifyPeer($sslConfig['verify_peer'] ?? true);
        $sslOptions->setCaFile($sslConfig['ca_file'] ?? null);
        $sslOptions->setCiphers($sslConfig['ciphers'] ?? null);
        $sslOptions->setAllowSelfSigned($sslConfig['allow_self_signed'] ?? false);

        return $sslOptions;
    }

    /** @throws \InvalidArgumentException */
    protected function level(array $config): int
    {
        $level = $config['level'] ?? 'debug';

        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }

    protected function parseProcessors(array $config): array
    {
        $processors = [];

        if (isset($config['processors']) && is_array($config['processors'])) {
            foreach ($config['processors'] as $processor) {
                $processors[] = $processor;
            }
        }

        return $processors;
    }

    protected function parseChannel(array $config): string
    {
        if (! isset($config['name'])) {
            return $this->getFallbackChannelName();
        }

        return $config['name'];
    }

    protected function getFallbackChannelName(): string
    {
        return $this->app->bound('env') ? $this->app->environment() : 'production';
    }
}
