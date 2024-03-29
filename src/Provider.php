<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

interface Provider
{
    /**
     * @return $this
     */
    public function setEventDispatcher(
        Dispatcher $dispatcher
    ): static;

    public function getEventDispatcher(): Dispatcher;
    public function isRunning(): bool;
}
