<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

/**
 * @property string $id
 * @property bool $persistent
 * @property bool $frozen
 * @property callable $handler
 * @property mixed $resource
 * @property Dispatcher $dispatcher
 */
interface Binding
{
    public function getId(): string;
    public function getType(): string;
    public function isPersistent(): bool;
    public function getHandler(): callable;
    public function getDispatcher(): Dispatcher;

    /**
     * @return $this
     */
    public function setEventResource(mixed $resource): static;

    public function getEventResource(): mixed;

    /**
     * @return $this
     */
    public function freeze(): static;

    /**
     * @return $this
     */
    public function unfreeze(): static;

    /**
     * @return $this
     */
    public function setFrozen(bool $frozen): static;

    /**
     * @return $this
     */
    public function markFrozen(bool $frozen): static;

    public function isFrozen(): bool;

    /**
     * @return $this
     */
    public function destroy(): static;

    /**
     * @return $this
     */
    public function trigger(mixed $targetResource): static;
}
