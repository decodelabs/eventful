<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

trait BindingTrait
{
    public string $id;
    public bool $persistent = true;
    public bool $frozen = false;

    /**
     * @var callable
     */
    public $handler;

    public mixed $resource;
    public Dispatcher $dispatcher;

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
        $this->handler = $handler;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get designated id for type
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Will this binding persist after use?
     */
    public function isPersistent(): bool
    {
        return $this->persistent;
    }

    /**
     * Get handler callback
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    /**
     * Get parent event loop
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }


    /**
     * Set event lib resource
     *
     * @param mixed $resource
     * @return $this
     */
    public function setEventResource(mixed $resource): static
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Get event lib resource
     */
    public function getEventResource(): mixed
    {
        return $this->resource;
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
     * Toggle freezing
     *
     * @return $this
     */
    public function setFrozen(bool $frozen): static
    {
        if ($frozen) {
            $this->freeze();
        } else {
            $this->unfreeze();
        }

        return $this;
    }

    /**
     * Actually mark this binding as frozen - should only be used internally
     *
     * @return $this
     */
    public function markFrozen(bool $frozen): static
    {
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
