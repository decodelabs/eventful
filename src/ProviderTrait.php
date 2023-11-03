<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use DecodeLabs\Exceptional;

trait ProviderTrait
{
    protected Dispatcher $eventDispatcher;

    /**
     * Replace current active event loop
     *
     * @return $this
     */
    public function setEventDispatcher(
        Dispatcher $dispatcher
    ): static {
        if ($this->isRunning()) {
            throw Exceptional::Runtime(
                'You cannot change the event dispatcher while it is running'
            );
        }

        $this->eventDispatcher = $dispatcher;
        return $this;
    }

    /**
     * Get current active event loop
     */
    public function getEventDispatcher(): Dispatcher
    {
        if (!$this->eventDispatcher) {
            throw Exceptional::Runtime(
                'No event dispatcher has been deployed yet'
            );
        }

        return $this->eventDispatcher;
    }

    /**
     * Check if event loop is running
     */
    public function isRunning(): bool
    {
        return $this->eventDispatcher && $this->eventDispatcher->isListening();
    }
}
