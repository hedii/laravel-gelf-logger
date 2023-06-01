<?php

namespace Hedii\LaravelGelfLogger\Tests\Fake;

use Monolog\LogRecord;

class AnotherTestProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return $record;
    }
}
