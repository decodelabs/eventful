<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use DecodeLabs\Exceptional;

/**
 * @phpstan-require-implements Provider
 */
trait ProviderTrait
{
    public Dispatcher $eventDispatcher {
        get {
            if (!isset($this->eventDispatcher)) {
                throw Exceptional::Runtime(
                    message: 'No event dispatcher has been deployed yet'
                );
            }

            return $this->eventDispatcher;
        }
        set {
            if (
                isset($this->eventDispatcher) &&
                $this->eventDispatcher->isListening()
            ) {
                throw Exceptional::Runtime(
                    message: 'You cannot change the event dispatcher while it is running'
                );
            }

            $this->eventDispatcher = $value;
        }
    }

    /**
     * Check if event loop is running
     */
    public function isRunning(): bool
    {
        return $this->eventDispatcher->isListening();
    }
}
