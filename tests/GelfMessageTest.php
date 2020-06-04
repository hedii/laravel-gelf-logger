<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Hedii\LaravelGelfLogger\GelfLoggerFactory;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class GelfMessageTest
 * @package Hedii\LaravelGelfLogger\Tests
 */
class GelfMessageTest extends Orchestra
{
    /**
     * dataProvider
     */
    public function message_prefix_data_provider(): array
    {
        return [
            'Add context and extra prefix' => [
                'parameters'        => [
                    'context_prefix'   => 'ctxt_',
                    'context_variable' => 'id',
                    'extra_prefix'     => 'extra_',
                    'extra_variable'   => 'ip'
                ],
                'expected_response' => [
                    'context_response' => 'ctxt_id',
                    'extra_response'   => 'extra_ip'
                ]
            ],
            'Empty context and extra config values' => [
                'parameters'        => [
                    'context_variable' => 'id',
                    'extra_variable'   => 'ip'
                ],
                'expected_response' => [
                    'context_response' => 'id',
                    'extra_response'   => 'ip'
                ]
            ],
            'Empty extra config value' => [
                'parameters'        => [
                    'context_prefix'   => 'ctxt_',
                    'context_variable' => 'id',
                    'extra_variable'   => 'ip'
                ],
                'expected_response' => [
                    'context_response' => 'ctxt_id',
                    'extra_response'   => 'ip'
                ]
            ],
            'Empty context config value' => [
                'parameters'        => [
                    'context_variable' => 'id',
                    'extra_variable'   => 'ip',
                    'extra_prefix'     => 'extra_'
                ],
                'expected_response' => [
                    'context_response' => 'id',
                    'extra_response'   => 'extra_ip'
                ]
            ],
            'Null context and extra config values' => [
                'parameters'        => [
                    'context_prefix'   => null,
                    'context_variable' => 'id',
                    'extra_prefix'     => null,
                    'extra_variable'   => 'ip'
                ],
                'expected_response' => [
                    'context_response' => 'id',
                    'extra_response'   => 'ip'
                ]
            ],
        ];
    }

    /**
     * @dataProvider message_prefix_data_provider
     *
     * @param array $parameters
     * @param array $expectedResponse
     *
     * @test
     */
    public function it_should_append_prefixes_to_gelf_message_variables(array $parameters, array $expectedResponse): void
    {
        $loggerConfig = [
            'system_name'    => 'my-system-namex',
            'driver'         => 'custom',
            'via'            => GelfLoggerFactory::class,
        ];

        if (isset($parameters['context_prefix'])){
            $loggerConfig['context_prefix'] = $parameters['context_prefix'];
        }

        if (isset($parameters['extra_prefix'])){
            $loggerConfig['extra_prefix'] = $parameters['extra_prefix'];
        }

        $this->app['config']->set('logging.channels.gelf', $loggerConfig);

        $logger = Log::channel('gelf');

        $formattedMessage = $logger->getHandlers()[0]->getFormatter()->format([
            'datetime' => '1591097093.0',
            'message'  => 'test',
            'level'    => 100,
            'extra'    => [$parameters['extra_variable'] => '127.0.0.1'],
            'context'  => [$parameters['context_variable'] => '777']
        ]);

        $this->assertArrayHasKey($expectedResponse['extra_response'], $formattedMessage->getAllAdditionals());
        $this->assertArrayHasKey($expectedResponse['context_response'], $formattedMessage->getAllAdditionals());
    }
}
