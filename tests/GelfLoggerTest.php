<?php

declare(strict_types=1);

namespace Nuwber\HypervelGelfLogger\Tests;

use Gelf\Publisher;
use Gelf\Transport\HttpTransport;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\SslOptions;
use Gelf\Transport\TcpTransport;
use Gelf\Transport\UdpTransport;
use Nuwber\HypervelGelfLogger\GelfLoggerFactory;
use LogicException;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;

class GelfLoggerTest extends TestCase
{
    #[Test]
    public function it_should_have_a_gelf_log_channel(): void
    {
        $logger = $this->createLogger();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame('my-custom-name', $logger->getName());
        $this->assertCount(1, $logger->getHandlers());

        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(GelfHandler::class, $handler);
        $this->assertSame(Level::Notice, $handler->getLevel());
        $this->assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertInstanceOf(Publisher::class, $publisher);
        $this->assertInstanceOf(UdpTransport::class, $transport);
    }

    #[Test]
    public function it_should_not_have_any_processor_if_the_config_does_not_have_processors(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You tried to pop from an empty processor stack.');

        $logger = $this->createLogger();
        $handler = $logger->getHandlers()[0];

        $handler->popProcessor();
    }

    #[Test]
    public function it_should_set_system_name_to_current_hostname_if_system_name_is_null(): void
    {
        $logger = $this->createLogger(['system_name' => null]);

        $this->assertSame(
            gethostname(),
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'systemName')
        );
    }

    #[Test]
    public function it_should_set_system_name_to_custom_value_if_system_name_config_is_provided(): void
    {
        $logger = $this->createLogger(['system_name' => 'my-system-name']);

        $this->assertSame(
            'my-system-name',
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'systemName')
        );
    }

    #[Test]
    public function it_should_call_the_tcp_transport_method_when_provided(): void
    {
        $logger = $this->createLogger(['transport' => 'tcp']);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertInstanceOf(TcpTransport::class, $transport);
    }

    #[Test]
    public function it_should_call_the_udp_transport_method_when_nothing_is_provided(): void
    {
        $logger = $this->createLogger();
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertInstanceOf(UdpTransport::class, $transport);
    }

    #[Test]
    public function it_should_set_max_length_if_max_length_is_provided(): void
    {
        $logger = $this->createLogger(['max_length' => 9999]);

        $this->assertSame(
            9999,
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'maxLength')
        );
    }

    #[Test]
    public function it_should_use_default_max_length_when_max_length_is_not_provided(): void
    {
        $logger = $this->createLogger();

        $this->assertSame(
            $this->getConstant(GelfMessageFormatter::class, 'DEFAULT_MAX_LENGTH'),
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'maxLength')
        );
    }

    #[Test]
    public function it_should_use_default_max_length_when_max_length_is_null(): void
    {
        $logger = $this->createLogger(['max_length' => null]);

        $this->assertSame(
            $this->getConstant(GelfMessageFormatter::class, 'DEFAULT_MAX_LENGTH'),
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'maxLength')
        );
    }

    #[Test]
    public function it_should_call_the_http_transport_method_when_provided(): void
    {
        $logger = $this->createLogger(['transport' => 'http']);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertInstanceOf(HttpTransport::class, $transport);
    }

    #[Test]
    public function it_should_set_path_if_path_is_provided(): void
    {
        $logger = $this->createLogger([
            'transport' => 'http',
            'path' => '/custom-path',
        ]);

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertSame('/custom-path', $this->getAttribute($transport, 'path'));
    }

    #[Test]
    public function it_should_set_path_to_default_path_if_path_is_null(): void
    {
        $logger = $this->createLogger([
            'transport' => 'http',
            'path' => null,
        ]);

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertSame(
            $this->getConstant(HttpTransport::class, 'DEFAULT_PATH'),
            $this->getAttribute($transport, 'path')
        );
    }

    #[Test]
    public function it_should_set_path_to_default_path_if_path_is_not_provided(): void
    {
        $logger = $this->createLogger(['transport' => 'http']);

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertSame(
            $this->getConstant(HttpTransport::class, 'DEFAULT_PATH'),
            $this->getAttribute($transport, 'path')
        );
    }

    #[Test]
    public function it_should_set_the_ssl_options_for_tcp_transport(): void
    {
        $logger = $this->createLogger([
            'transport' => 'tcp',
            'port' => 12202,
            'ssl' => true,
            'ssl_options' => [
                'verify_peer' => false,
                'ca_file' => '/path/to/ca.pem',
                'ciphers' => 'TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256',
                'allow_self_signed' => true,
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        /** @var SslOptions $sslOptions */
        $sslOptions = $this->getAttribute($transport, 'sslOptions');

        $this->assertFalse($sslOptions->getVerifyPeer());
        $this->assertTrue($sslOptions->getAllowSelfSigned());
        $this->assertEquals('/path/to/ca.pem', $sslOptions->getCaFile());
        $this->assertEquals('TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256', $sslOptions->getCiphers());
    }

    #[Test]
    public function it_should_set_the_ssl_options_for_http_transport(): void
    {
        $logger = $this->createLogger([
            'transport' => 'http',
            'port' => 443,
            'ssl' => true,
            'ssl_options' => [
                'verify_peer' => false,
                'ca_file' => '/path/to/ca.pem',
                'ciphers' => 'TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256',
                'allow_self_signed' => true,
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        /** @var SslOptions $sslOptions */
        $sslOptions = $this->getAttribute($transport, 'sslOptions');

        $this->assertFalse($sslOptions->getVerifyPeer());
        $this->assertTrue($sslOptions->getAllowSelfSigned());
        $this->assertSame('/path/to/ca.pem', $sslOptions->getCaFile());
        $this->assertSame('TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256', $sslOptions->getCiphers());
    }


    #[Test]
    public function it_should_not_add_ssl_on_tcp_transport_when_the_ssl_config_is_missing_or_set_to_false(): void
    {
        $logger = $this->createLogger([
            'transport' => 'tcp',
            'port' => 1234,
            'ssl_options' => [
                'verify_peer' => false,
                'ca_file' => '/path/to/ca.pem',
                'ciphers' => 'TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256',
                'allow_self_signed' => true,
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertNull($this->getAttribute($transport, 'sslOptions'));

        $logger = $this->createLogger([
            'transport' => 'tcp',
            'port' => 1234,
            'ssl' => false,
            'ssl_options' => [
                'verify_peer' => false,
                'ca_file' => '/path/to/ca.pem',
                'ciphers' => 'TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256',
                'allow_self_signed' => true,
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertNull($this->getAttribute($transport, 'sslOptions'));
    }

    #[Test]
    public function it_should_not_add_ssl_on_http_transport_when_the_ssl_config_is_missing_or_set_to_false(): void
    {
        $logger = $this->createLogger([
            'transport' => 'http',
            'ssl_options' => [
                'verify_peer' => false,
                'ca_file' => '/path/to/ca.pem',
                'ciphers' => 'TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256',
                'allow_self_signed' => true,
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertNull($this->getAttribute($transport, 'sslOptions'));

        $logger = $this->createLogger([
            'transport' => 'http',
            'ssl' => false,
            'ssl_options' => [
                'verify_peer' => false,
                'ca_file' => '/path/to/ca.pem',
                'ciphers' => 'TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256',
                'allow_self_signed' => true,
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertNull($this->getAttribute($transport, 'sslOptions'));
    }

    #[Test]
    public function it_should_use_the_default_ssl_options_when_ssl_options_is_missing(): void
    {
        $logger = $this->createLogger([
            'transport' => 'tcp',
            'port' => 12202,
            'ssl' => true,
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        /** @var SslOptions $sslOptions */
        $sslOptions = $this->getAttribute($transport, 'sslOptions');

        $this->assertTrue($sslOptions->getVerifyPeer());
        $this->assertFalse($sslOptions->getAllowSelfSigned());
        $this->assertNull($sslOptions->getCaFile());
        $this->assertNull($sslOptions->getCiphers());

        $logger = $this->createLogger([
            'transport' => 'http',
            'port' => 443,
            'ssl' => true,
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        /** @var SslOptions $sslOptions */
        $sslOptions = $this->getAttribute($transport, 'sslOptions');

        $this->assertTrue($sslOptions->getVerifyPeer());
        $this->assertFalse($sslOptions->getAllowSelfSigned());
        $this->assertNull($sslOptions->getCaFile());
        $this->assertNull($sslOptions->getCiphers());
    }

    #[Test]
    public function it_should_ignore_errors_when_the_ignore_error_config_is_missing_or_set_to_true(): void
    {
        // Test when ignore_error is not set (defaults to true in factory)
        $config = $this->config;
        unset($config['ignore_error']);
        $factory = new GelfLoggerFactory($this->container);
        $logger = $factory($config);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertInstanceOf(IgnoreErrorTransportWrapper::class, $transport);

        $logger = $this->createLogger(['ignore_error' => true]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertInstanceOf(IgnoreErrorTransportWrapper::class, $transport);
    }

    #[Test]
    public function it_should_not_ignore_error_if_ignore_error_config_is_set_to_false(): void
    {
        $logger = $this->createLogger(['ignore_error' => false]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertInstanceOf(UdpTransport::class, $transport);
    }

    #[Test]
    public function it_should_not_set_authentication_on_http_transport_if_http_basic_auth_is_not_fully_provided(): void
    {
        $logger = $this->createLogger(['transport' => 'http']);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];
        $this->assertNull($this->getAttribute($transport, 'authentication'));

        $logger = $this->createLogger([
            'transport' => 'http',
            'http_basic_auth' => 'foo',
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];
        $this->assertNull($this->getAttribute($transport, 'authentication'));

        $logger = $this->createLogger([
            'transport' => 'http',
            'http_basic_auth' => [
                'username' => '',
                'password' => 'my_password',
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];
        $this->assertNull($this->getAttribute($transport, 'authentication'));

        $logger = $this->createLogger([
            'transport' => 'http',
            'http_basic_auth' => [
                'username' => 'my_username',
                'password' => '',
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];
        $this->assertNull($this->getAttribute($transport, 'authentication'));

        $logger = $this->createLogger([
            'transport' => 'http',
            'http_basic_auth' => [
                'username' => 'my_username',
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];
        $this->assertNull($this->getAttribute($transport, 'authentication'));

        $logger = $this->createLogger([
            'transport' => 'http',
            'http_basic_auth' => [
                'password' => 'my_password',
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];
        $this->assertNull($this->getAttribute($transport, 'authentication'));
    }

    #[Test]
    public function it_should_set_authentication_on_http_transport_if_http_basic_auth_is_provided(): void
    {
        $logger = $this->createLogger([
            'transport' => 'http',
            'http_basic_auth' => [
                'username' => 'my_username',
                'password' => 'my_password',
            ],
        ]);
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $publisher->getTransports()[0];

        $this->assertSame('my_username:my_password', $this->getAttribute($transport, 'authentication'));
    }
}
