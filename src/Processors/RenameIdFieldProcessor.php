<?php

namespace Hedii\LaravelGelfLogger\Processors;

class RenameIdFieldProcessor
{
    /**
     * Rename "id" field  to "_id" (additional field 'id' is not allowed).
     *
     * @see https://github.com/hedii/laravel-gelf-logger/issues/33
     */
    public function __invoke(array $record): array
    {
        foreach ($record['context'] as $key => $value) {
            if ($key === 'id' && ! array_key_exists('_id', $record['context'])) {
                unset($record['context']['id']);

                $record['context']['_id'] = $value;
            }
        }

        return $record;
    }
}
