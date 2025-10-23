<?php

declare(strict_types=1);

namespace Nuwber\HypervelGelfLogger\Tests\Fake;

use Monolog\LogRecord;

class AnotherTestProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return $record;
    }
}
