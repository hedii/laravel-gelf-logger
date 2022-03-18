<?php

namespace Hedii\LaravelGelfLogger\Tests\Fake;

class TestProcessor
{
    public function __invoke(array $record): array
    {
        return $record;
    }
}
