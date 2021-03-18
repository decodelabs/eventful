<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

interface Provider
{
    public function setEventDispatcher(Dispatcher $dispatcher): Provider;
    public function getEventDispatcher(): Dispatcher;
    public function isRunning(): bool;
}
