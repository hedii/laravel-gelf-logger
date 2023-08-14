<?php

namespace Hedii\LaravelGelfLogger\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class NullStringProcessor implements ProcessorInterface
{
    /**
     * Transform a "NULL" string record into a null value.
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;

        foreach ($context as $key => $value) {
            if (is_string($value) && strtoupper($value) === 'NULL') {
                $context[$key] = null;
            }
        }

        return $record->with(context: $context);
    }
}
