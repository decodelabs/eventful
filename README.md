# Eventful

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/eventful?style=flat)](https://packagist.org/packages/decodelabs/eventful)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/eventful.svg?style=flat)](https://packagist.org/packages/decodelabs/eventful)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/eventful.svg?style=flat)](https://packagist.org/packages/decodelabs/eventful)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/eventful/integrate.yml?branch=develop)](https://github.com/decodelabs/eventful/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/eventful?style=flat)](https://packagist.org/packages/decodelabs/eventful)

### Asynchronous IO event dispatcher for PHP

Eventful provides an extensible IO event dispatcher for use in interactive and asynchronous processes.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---


## Installation

Install the library via composer:

```bash
composer require decodelabs/eventful
```

### Usage

Listen for events on IO, Signals and Timers and respond accordingly.
If php's Event extension is available, that will be used, otherwise a basic <code>select()</code> loop fills in the gaps.

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Eventful\Factory;

$broker = Deliverance::newCliBroker();

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
