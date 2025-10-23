<?php

declare(strict_types=1);

namespace Nuwber\HypervelGelfLogger\Tests\Fake;

use Monolog\LogRecord;

class TestProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return $record;
    }
}
