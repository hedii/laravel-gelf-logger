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
use Illuminate\Log\ParsesLogConfiguration;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Logger;

class GelfLoggerFactory
{
    use ParsesLogConfiguration;

    public function __construct(protected Container $app)
    {
    }

    public function __invoke(array $config): Logger
    {
        $config = $this->parseConfig($config);

        $transport = $this->getTransport(
            $config['transport'],
            $config['host'],
            $config['port'],
            $config['chunk_size'],
            $config['path'],
            $this->enableSsl($config) ? $this->sslOptions($config['ssl_options']) : null
        );

        if ($config['ignore_error']) {
            $transport = new IgnoreErrorTransportWrapper($transport);
        }

        $handler = new GelfHandler(new Publisher($transport), $this->level($config));

        $handler->setFormatter(
            new GelfMessageFormatter(
                $config['system_name'],
                $config['extra_prefix'],
                $config['context_prefix'],
                $config['max_length']
            )
        );

        foreach ($this->parseProcessors($config) as $processor) {
            $handler->pushProcessor(new $processor);
        }

        return new Logger($this->parseChannel($config), [$handler]);
    }

    protected function parseConfig(array $config): array
    {
        $config['transport'] ??= 'udp';
        $config['host'] ??= '127.0.0.1';
        $config['port'] ??= 12201;
        $config['chunk_size'] ??= UdpTransport::CHUNK_SIZE_WAN;
        $config['path'] ??= null;
        $config['system_name'] ??= null;
        $config['extra_prefix'] ??= null;
        $config['context_prefix'] ??= '';
        $config['max_length'] ??= null;
        $config['ignore_error'] ??= true;
        $config['ssl'] ??= false;
        $config['ssl_options'] ??= null;

        if ($config['ssl_options']) {
            $config['ssl_options']['verify_peer'] ??= true;
            $config['ssl_options']['ca_file'] ??= null;
            $config['ssl_options']['ciphers'] ??= null;
            $config['ssl_options']['allow_self_signed'] ??= false;
        }

        return $config;
    }

    protected function getTransport(
        string $transport,
        string $host,
        int $port,
        int $chunkSize,
        ?string $path = null,
        ?SslOptions $sslOptions = null
    ): AbstractTransport {
        return match (strtolower($transport)) {
            'tcp' => new TcpTransport($host, $port, $sslOptions),
            'http' => $path
                ? new HttpTransport($host, $port, $path, $sslOptions)
                : new HttpTransport($host, $port, sslOptions: $sslOptions),
            default => new UdpTransport($host, $port, $chunkSize),
        };
    }

    protected function enableSsl(array $config): bool
    {
        if (! isset($config['transport']) || $config['transport'] === 'udp') {
            return false;
        }

        return $config['ssl'];
    }

    protected function sslOptions(?array $sslConfig = null): SslOptions
    {
        $sslOptions = new SslOptions();

        if (! $sslConfig) {
            return $sslOptions;
        }

        $sslOptions->setVerifyPeer($sslConfig['verify_peer']);
        $sslOptions->setCaFile($sslConfig['ca_file']);
        $sslOptions->setCiphers($sslConfig['ciphers']);
        $sslOptions->setAllowSelfSigned($sslConfig['allow_self_signed']);

        return $sslOptions;
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

    protected function getFallbackChannelName(): string
    {
        return $this->app->bound('env') ? $this->app->environment() : 'production';
    }
}
