<?php

if (! function_exists('gelf')) {
    /**
     * @return \Hedii\LaravelGelfLogger\GelfLogger
     */
    function gelf()
    {
        return app(\Hedii\LaravelGelfLogger\GelfLogger::class);
    }
}
