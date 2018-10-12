<?php

namespace Hedii\LaravelGelfLogger\Tests\Fake;

class TestProcessor
{
    /**
     * A fake processor that do nothing.
     *
     * @param array $record
     * @return array
     */
    public function __invoke(array $record): array
    {
        return $record;
    }
}
