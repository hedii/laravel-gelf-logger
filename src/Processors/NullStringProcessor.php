<?php

namespace Hedii\LaravelGelfLogger\Processors;

use Monolog\LogRecord;

class NullStringProcessor
{
    /**
     * Transform a "NULL" string record into a null value.
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $newContext = [];

        foreach ($record->context as $key => $value) {
            if (is_string($value) && strtoupper($value) === 'NULL') {
                $newContext[$key] = null;
            } else {
                $newContext[$key] = $value;
            }
        }

        return $record->with(context: $newContext);
    }
}
