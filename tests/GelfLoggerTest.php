<?php

namespace Hedii\LaravelGelfLogger\Tests;

use Gelf\Logger;
use Hedii\LaravelGelfLogger\GelfLogger;

class GelfLoggerTest extends TestCase
{
    public function test_it_should_return_a_logger_instance()
    {
        $this->assertInstanceOf(Logger::class, new GelfLogger());
    }

    public function test_it_should_return_a_logger_instance_when_the_helper_is_used()
    {
        $this->assertInstanceOf(Logger::class, gelf());
    }
}