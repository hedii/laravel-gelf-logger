[![Build Status](https://travis-ci.org/hedii/laravel-gelf-logger.svg?branch=master)](https://travis-ci.org/hedii/laravel-gelf-logger)

# Laravel Gelf Logger

A package to send [gelf](http://docs.graylog.org/en/2.1/pages/gelf.html) logs to a gelf compatible backend like graylog. It is a Laravel wrapper for [bzikarsky/gelf-php](https://github.com/bzikarsky/gelf-php) package.

It uses the new [Laravel custom log channel](https://laravel.com/docs/master/logging) introduced in Laravel 5.6.

A gelf receiver like graylog2 must be configured to receive messages with a GELF UDP or TCP Input.

## Table of contents

- [Table of contents](#table-of-contents)
- [Installation](#installation)
- [Usage](#usage)
  - [Example](#example)
- [Testing](#testing)
- [License](#license)

## Installation

Install via [composer](https://getcomposer.org/doc/00-intro.md)

```sh
composer require hedii/laravel-gelf-logger
```

Edit `config/logging.php` to add the new `gelf` log channel.

```php
return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        // You can use the gelf log channel with the stack log channel
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'gelf'],
        ],

        // other log channels...

        'gelf' => [
            'driver' => 'custom',

            'via' => \Hedii\LaravelGelfLogger\GelfLoggerFactory::class,

            // This optional option determines the processors that should be
            // pushed to the handler. This option is useful to modify a field
            // in the log context (see NullStringProcessor), or to add extra
            // data. Each processor must be a callable or an object with an
            // __invoke method: see monolog documentation about processors.
            // Default is an empty array.
            'processors' => [
                \Hedii\LaravelGelfLogger\Processors\NullStringProcessor::class,
                \Foo\Bar\AnotherProcessor::class,
            ],

            // This optional option determines the minimum "level" a message
            // must be in order to be logged by the channel. Default is 'debug'
            'level' => 'debug',

            // This optional option determines the channel name sent with the
            // message in the 'facility' field. Default is equal to app.env
            // configuration value
            'name' => 'my-custom-name',

            // This optional option determines the system name sent with the
            // message in the 'source' field. When forgotten or set to null,
            // the current hostname is used.
            'system_name' => null,

            // This optional option determines if you want the TCP transport
            // for the gelf log messages. Default is UDP
            'transport' => 'udp',

            // This optional option determines the host that will receive the
            // gelf log messages. Default is 127.0.0.1
            'host' => '127.0.0.1',

            // This optional option determines the port on which the gelf
            // receiver host is listening. Default is 12201
            'port' => 12201,

            // This optional option determines the maximum length per message
            // field. When forgotten or set to null, the default value of 
            // \Monolog\Formatter\GelfMessageFormatter::DEFAULT_MAX_LENGTH is
            // used (currently this value is 32766)
            'max_length' => null,
        ],
    ],
];
```

## Usage

Once you have modified the Laravel logging configuration, you can use the gelf log channel [as any Laravel log channel](https://laravel.com/docs/master/logging#writing-log-messages).

### Example

```php
// Explicitly use the gelf channel
Log::channel('gelf')->debug($message, ['foo' => 'bar']);
Log::channel('gelf')->emergency($message, ['foo' => 'bar']);

// In case of a stack log channel containing the gelf log channel and stack
// configured as the default log channel
Log::notice($message, ['foo' => 'bar']);

// Using the logger helper
logger($message, $context);
```

## Testing

```
composer test
```

## License

laravel-gelf-logger is released under the MIT Licence. See the bundled [LICENSE](https://github.com/hedii/laravel-gelf-logger/blob/master/LICENSE.md) file for details.
