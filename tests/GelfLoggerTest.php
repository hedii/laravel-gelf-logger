<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Exception;
use Gelf\Publisher;
use Gelf\Transport\SslOptions;
use Gelf\Transport\HttpTransport;
use Hedii\LaravelGelfLogger\GelfLoggerFactory;
use Illuminate\Support\Facades\Log;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Logger;
use Orchestra\Testbench\TestCase as Orchestra;
use Gelf\Transport\TcpTransport;
use Gelf\Transport\UdpTransport;
use ReflectionClass;

class GelfLoggerTest extends Orchestra
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('logging.default', 'gelf');
        $app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'level' => 'notice',
            'name' => 'my-custom-name',
            'host' => '127.0.0.2',
            'port' => 12202
        ]);
    }

    /** @test */
    public function it_should_have_a_gelf_log_channel(): void
    {
        $logger = Log::channel('gelf');

        $this->assertInstanceOf(Logger::class, $logger->getLogger());
        $this->assertSame($logger->getName(), 'my-custom-name');
        $this->assertCount(1, $logger->getHandlers());

        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(GelfHandler::class, $handler);
        $this->assertSame(Logger::NOTICE, $handler->getLevel());
        $this->assertInstanceOf(GelfMessageFormatter::class, $handler->getFormatter());

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

        $this->assertInstanceOf(Publisher::class, $publisher);
        $this->assertInstanceOf(UdpTransport::class, $transport);
    }

    /** @test */
    public function it_should_not_have_any_processor_if_the_config_does_not_have_processors(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You tried to pop from an empty processor stack.');

        $logger = Log::channel('gelf');
        $handler = $logger->getHandlers()[0];

        $handler->popProcessor();
    }

    /** @test */
    public function it_should_set_system_name_to_current_hostname_if_system_name_is_null(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'system_name' => null,
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class
        ]);

        $logger = Log::channel('gelf');

        $this->assertSame(
            gethostname(),
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'systemName')
        );
    }

    /** @test */
    public function it_should_set_system_name_to_custom_value_if_system_name_config_is_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'system_name' => 'my-system-name',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class
        ]);

        $logger = Log::channel('gelf');

        $this->assertSame(
            'my-system-name',
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'systemName')
        );
    }

    /** @test */
    public function it_should_call_the_tcp_transport_method_when_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'transport' => 'tcp',
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class
        ]);

        $logger = Log::channel('gelf');
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

        $this->assertInstanceOf(TcpTransport::class, $transport);
    }

    /** @test */
    public function it_should_call_the_udp_transport_method_when_nothing_is_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class
        ]);

        $logger = Log::channel('gelf');
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

        $this->assertInstanceOf(UdpTransport::class, $transport);
    }

    /** @test */
    public function it_should_set_max_length_if_max_length_is_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'max_length' => 9999
        ]);

        $logger = Log::channel('gelf');

        $this->assertSame(
            9999,
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'maxLength')
        );
    }

    /** @test */
    public function it_should_use_default_max_length_when_max_length_is_not_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class
        ]);

        $logger = Log::channel('gelf');

        $this->assertSame(
            $this->getConstant(GelfMessageFormatter::class, 'DEFAULT_MAX_LENGTH'),
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'maxLength')
        );
    }

    /** @test */
    public function it_should_use_default_max_length_when_max_length_is_null(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'max_length' => null
        ]);

        $logger = Log::channel('gelf');

        $this->assertSame(
            $this->getConstant(GelfMessageFormatter::class, 'DEFAULT_MAX_LENGTH'),
            $this->getAttribute($logger->getHandlers()[0]->getFormatter(), 'maxLength')
        );
    }

    /** @test */
    public function it_should_call_the_http_transport_method_when_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'transport' => 'http'
        ]);

        $logger = Log::channel('gelf');
        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

        $this->assertInstanceOf(HttpTransport::class, $transport);
    }

    /** @test */
    public function it_should_set_path_if_path_is_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'transport' => 'http',
            'path' => '/custom-path'
        ]);

        $logger = Log::channel('gelf');

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

        $this->assertSame('/custom-path', $this->getAttribute($transport, 'path'));
    }

    /** @test */
    public function it_should_set_path_to_default_path_if_path_is_null(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'transport' => 'http',
            'path' => null
        ]);

        $logger = Log::channel('gelf');

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

        $this->assertSame(HttpTransport::DEFAULT_PATH, $this->getAttribute($transport, 'path'));
    }

    /** @test */
    public function it_should_set_path_to_default_path_if_path_is_not_provided(): void
    {
        $this->app['config']->set('logging.channels.gelf', [
            'driver' => 'custom',
            'via' => GelfLoggerFactory::class,
            'transport' => 'http'
        ]);

        $logger = Log::channel('gelf');

        $publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
        $transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

        $this->assertSame(HttpTransport::DEFAULT_PATH, $this->getAttribute($transport, 'path'));
    }

	/** @test */
	public function it_should_set_the_ssl_options_for_tcp_connections()
	{
		$this->app['config']->set('logging.channels.gelf', [
			'driver' => 'custom',
			'via' => GelfLoggerFactory::class,
			'transport' => 'tcp',
			'ssl' => [
				'verify_peer' => false,
				'ca_file' => '/path/to/ca.pem',
				'ciphers' => 'TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256',
				'allow_self_signed' => true,
			],
		]);

		$logger = Log::channel('gelf');
		$publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
		$transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

		/** @var SslOptions $sslOptions */
		$sslOptions = $this->getAttribute($transport, 'sslOptions');

		$this->assertFalse($sslOptions->getVerifyPeer());
		$this->assertTrue($sslOptions->getAllowSelfSigned());
		$this->assertEquals('/path/to/ca.pem', $sslOptions->getCaFile());
		$this->assertEquals('TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256', $sslOptions->getCiphers());
	}

	/** @test */
	public function it_should_not_add_ssl_on_tcp_when_the_ssl_config_is_missing()
	{
		$this->app['config']->set('logging.channels.gelf', [
			'driver' => 'custom',
			'via' => GelfLoggerFactory::class,
			'transport' => 'tcp',
		]);

		$logger = Log::channel('gelf');
		$publisher = $this->getAttribute($logger->getHandlers()[0], 'publisher');
		$transport = $this->getAttribute($publisher->getTransports()[0], 'transport');

		$this->assertNull($this->getAttribute($transport, 'sslOptions'));
	}

    /**
     * Get protected or private attribute from an object.
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getAttribute(object $object, string $property)
    {
        try {
            $reflector = new ReflectionClass($object);
            $attribute = $reflector->getProperty($property);
            $attribute->setAccessible(true);

            return $attribute->getValue($object);
        } catch (Exception $e) {
            throw new Exception('Cannot get attribute from the provided object');
        }
    }

    /**
     * Get protected or private constant from a class.
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getConstant(string $class, string $constant)
    {
        try {
            $reflector = new ReflectionClass($class);

            return $reflector->getConstant($constant);
        } catch (Exception $e) {
            throw new Exception('Cannot get attribute from the provided class');
        }
    }
}
