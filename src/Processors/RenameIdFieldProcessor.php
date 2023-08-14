<?php

namespace Hedii\LaravelGelfLogger\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RenameIdFieldProcessor implements ProcessorInterface
{
    /**
     * Rename "id" field  to "_id" (additional field 'id' is not allowed).
     *
     * @see https://github.com/hedii/laravel-gelf-logger/issues/33
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;

        if (array_key_exists('id', $context)) {
            $context['_id'] = $context['id'];
            unset($context['id']);
        }

        return $record->with(context: $context);
    }
}
