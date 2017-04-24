[![Build Status](https://travis-ci.org/hedii/laravel-gelf-logger.svg?branch=master)](https://travis-ci.org/hedii/laravel-gelf-logger)

# Laravel Gelf Logger

A package to send [gelf](http://docs.graylog.org/en/2.1/pages/gelf.html) logs to a gelf compatible backend like graylog. It is a laravel wrapper for [bzikarsky/gelf-php](https://github.com/bzikarsky/gelf-php) package.

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

Add it to your providers array in `config/app.php`:

```php
Hedii\LaravelGelfLogger\LaravelGelfLoggerServiceProvider::class
```

If you want to use the facade, add it to your aliases array in `config/app.php`:

```php
'GelfLogger' => \Hedii\LaravelGelfLogger\Facades\GelfLogger::class
```

Publish the configuration file:

```sh
php artisan vendor:publish --provider="Hedii\LaravelGelfLogger\LaravelGelfLoggerServiceProvider"
```

See the content of the published configuration file in `config/gelf-logger.php` if you want to change the defaults.

```php
/**
 * The ip address of the log server. If the value below is null,
 * the default value '127.0.0.1' will be used.
 */
'host' => null,

/**
 * The udp port of the log server. If the value below is null,
 * the default value 12201 will be used.
 */
'port' => null
```

## Usage

See the [bzikarsky/gelf-php](https://github.com/bzikarsky/gelf-php/tree/master/examples) examples in his repo to find the available methods for the `gelf()` function.

### Example

```php
gelf()->alert('There was a foo in bar', ['foo' => 'bar']);
```

```php
try {
    throw new \Exception('test exception');
} catch (\Exception $e) {
    gelf()->emergency('Exception example', [
        'exception' => $e
    ]);
}
```

## Testing

```
composer test
```

## License

laravel-gelf-logger is released under the MIT Licence. See the bundled [LICENSE](https://github.com/hedii/laravel-gelf-logger/blob/master/LICENSE.md) file for details.
