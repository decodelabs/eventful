<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use Closure;

/**
 * @phpstan-require-implements Binding
 */
trait BindingTrait
{
    protected(set) string $id;
    protected(set) bool $persistent = true;

    protected(set) bool $frozen = false;

    protected(set) Closure $handler;

    public mixed $resource;
    protected(set) Dispatcher $dispatcher;

    /**
     * Init with ref to event loop, id, options and handler
     */
    public function __construct(
        Dispatcher $dispatcher,
        string $id,
        bool $persistent,
        callable $handler
    ) {
        $this->id = $id;
        $this->persistent = $persistent;
        $this->handler = Closure::fromCallable($handler);
        $this->dispatcher = $dispatcher;
    }

    /**
     * Freeze this binding
     *
     * @return $this
     */
    public function freeze(): static
    {
        $this->dispatcher->freezeBinding($this);
        return $this;
    }

    /**
     * Unfreeze this binding
     *
     * @return $this
     */
    public function unfreeze(): static
    {
        $this->dispatcher->unfreezeBinding($this);
        return $this;
    }


    /**
     * Actually mark this binding as frozen - should only be used internally
     *
     * @return $this
     */
    public function markFrozen(
        bool $frozen
    ): static {
        $this->frozen = $frozen;
        return $this;
    }

    /**
     * Has this binding been frozen?
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }
}
