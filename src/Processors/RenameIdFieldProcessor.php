<?php

namespace Hedii\LaravelGelfLogger\Processors;

use Monolog\LogRecord;

class RenameIdFieldProcessor
{
    /**
     * Rename "id" field  to "_id" (additional field 'id' is not allowed).
     *
     * @see https://github.com/hedii/laravel-gelf-logger/issues/33
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $newContext = $record->context;

        if (array_key_exists('id', $newContext)) {
            $newContext['_id'] = $newContext['id'];
            unset($newContext['id']);
        }

        return $record->with(context: $newContext);
    }
}
