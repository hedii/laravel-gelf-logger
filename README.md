# Hypervel Gelf Logger

A package to send [gelf](http://docs.graylog.org/en/2.1/pages/gelf.html) logs to a gelf compatible backend like graylog. It is a Hypervel wrapper for [bzikarsky/gelf-php](https://github.com/bzikarsky/gelf-php) package.

This package uses Hypervel's custom log channel system.

A gelf receiver like graylog2 must be configured to receive messages with a GELF UDP, TCP or HTTP Input.

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
composer require nuwber/hypervel-gelf-logger
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

            'via' => \Nuwber\HypervelGelfLogger\GelfLoggerFactory::class,

            // This optional option determines the processors that should be
            // pushed to the handler. This option is useful to modify a field
            // in the log context (see NullStringProcessor), or to add extra
            // data. Each processor must be a callable or an object with an
            // __invoke method: see monolog documentation about processors.
            // Default is an empty array.
            'processors' => [
                \Nuwber\HypervelGelfLogger\Processors\NullStringProcessor::class,
                \Nuwber\HypervelGelfLogger\Processors\RenameIdFieldProcessor::class,
                // another processor...
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

            // This optional option determines if you want the UDP, TCP or HTTP
            // transport for the gelf log messages. Default is UDP
            'transport' => 'udp',

            // This optional option determines the host that will receive the
            // gelf log messages. Default is 127.0.0.1
            'host' => '127.0.0.1',

            // This optional option determines the port on which the gelf
            // receiver host is listening. Default is 12201
            'port' => 12201,
            
            // This optional option determines the chunk size used when
            // transferring message via UDP transport. Default is 1420.
            'chunk_size' => 1420,

            // This optional option determines the path used for the HTTP
            // transport. When forgotten or set to null, default path '/gelf'
            // is used.
            'path' => null,
            
            // This optional option enable or disable ssl on TCP or HTTP
            // transports. Default is false.
            'ssl' => false,
            
            // If ssl is enabled, the following configuration is used.
            'ssl_options' => [
                // Enable or disable the peer certificate check. Default is
                // true.
                'verify_peer' => true,
                
                // Path to a custom CA file (eg: "/path/to/ca.pem"). Default
                // is null.
                'ca_file' => null,
                
                // List of ciphers the SSL layer may use, formatted as
                // specified in ciphers(1). Default is null.
                'ciphers' => null,
                
                // Whether self-signed certificates are allowed. Default is
                // false.
                'allow_self_signed' => false,
            ],
            
            // If you want to send messages to the gelf server using http basic
            // authentication, the following configuration is used. Only useful
            // if transport is set to http.
            'http_basic_auth' => [
                // The http basic authentication username.
                'username' => null,
                
                // The http basic authentication password.
                'password' => null,
            ],

            // This optional option determines the maximum length per message
            // field. When forgotten or set to null, the default value of 
            // \Monolog\Formatter\GelfMessageFormatter::DEFAULT_MAX_LENGTH is
            // used (currently this value is 32766)
            'max_length' => null,

            // This optional option determines the prefix for 'context' fields
            // from the Monolog record. Default is null (no context prefix)
            'context_prefix' => null,

            // This optional option determines the prefix for 'extra' fields
            // from the Monolog record. Default is null (no extra prefix)
            'extra_prefix' => null,
            
            // This optional option determines whether errors thrown during
            // logging should be ignored or not. Default is true.
            'ignore_error' => true,

        ],
    ],
];
```

## Usage

Once you have modified the Hypervel logging configuration, you can use the gelf log channel as any Hypervel log channel.

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

hypervel-gelf-logger is released under the MIT Licence. See the bundled LICENSE file for details.

## Credits

This package is a Hypervel port of [hedii/laravel-gelf-logger](https://github.com/hedii/laravel-gelf-logger).
