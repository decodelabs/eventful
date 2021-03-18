# Eventful

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/eventful?style=flat-square)](https://packagist.org/packages/decodelabs/eventful)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/eventful.svg?style=flat-square)](https://packagist.org/packages/decodelabs/eventful)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/eventful.svg?style=flat-square)](https://packagist.org/packages/decodelabs/eventful)
[![Build Status](https://img.shields.io/travis/com/decodelabs/eventful/main.svg?style=flat-square)](https://travis-ci.com/decodelabs/eventful)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/eventful?style=flat-square)](https://packagist.org/packages/decodelabs/eventful)

Asynchronous IO event dispatcher for PHP


## Installation

Install the library via composer:

```bash
composer require decodelabs/eventful
```

### Usage

Listen for events on IO, Signals and Timers and respond accordingly.
If php's Event extension is available, that will be used, otherwise a basic <code>select()</code> loop fills in the gaps.

```php
use DecodeLabs\Atlas;
use DecodeLabs\Eventful\Factory;

$broker = Atlas::newCliBroker();

$eventLoop = Factory::newDispatcher()

    // Run every 2 seconds
    ->bindTimer('timer1', 2, function() use($broker) {
        $broker->writeLine('Timer 1');
    })

    // Listen for reads, but frozen - won't activate until unfrozen
    ->bindStreamReadFrozen($input = $broker->getFirstInputReceiver(), function() use($broker) {
        $broker->writeLine('You said: '.$broker->readLine());
    })

    // Run once after 1 second
    ->bindTimerOnce('timer2', 1, function($binding) use($broker, $input) {
        $broker->writeLine('Timer 2');

        // Unfreeze io reads
        $binding->eventLoop->unfreeze($intput);
    })

    // Check if we want to bail every second
    ->setCycleHandler(function(int $cycles) {
        if($cycles > 10) {
            return false;
        }
    });


/*
Outputs something like:

Timer 2
Timer 1
Timer 1
You said: Hello world
Timer 1
*/
```


## Licensing
Eventful is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
