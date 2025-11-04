<?php

/**
 * Eventful
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

interface Provider
{
    public Dispatcher $eventDispatcher { get; set; }

    public function isRunning(): bool;
}
