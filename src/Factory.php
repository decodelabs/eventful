<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use DecodeLabs\Eventful\Dispatcher\Event as LibEventDispatcher;
use DecodeLabs\Eventful\Dispatcher\Select as SelectDispatcher;

class Factory
{
    /**
     * Create an event loop
     */
    public static function newDispatcher(): Dispatcher
    {
        if (extension_loaded('event')) {
            return new LibEventDispatcher();
        } else {
            return new SelectDispatcher();
        }
    }
}
