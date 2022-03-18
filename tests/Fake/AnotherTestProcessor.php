<?php

namespace Hedii\LaravelGelfLogger\Tests\Fake;

class AnotherTestProcessor
{
    public function __invoke(array $record): array
    {
        return $record;
    }
}
