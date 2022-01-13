<?php

namespace Hedii\LaravelGelfLogger\Processors;

class NullStringProcessor
{
    /**
     * Transform a "NULL" string record into a null value.
     */
    public function __invoke(array $record): array
    {
        foreach ($record['context'] as $key => $value) {
            if (is_string($value) && strtoupper($value) === 'NULL') {
                $record['context'][$key] = null;
            }
        }

        return $record;
    }
}
