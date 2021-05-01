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
     * @param mixed $resource
     * @return $this
     */
    public function setEventResource($resource): Binding;

    /**
     * @return mixed
     */
    public function getEventResource();

    /**
     * @return $this
     */
    public function freeze(): Binding;

    /**
     * @return $this
     */
    public function unfreeze(): Binding;

    /**
     * @return $this
     */
    public function setFrozen(bool $frozen): Binding;

    /**
     * @return $this
     */
    public function markFrozen(bool $frozen): Binding;

    public function isFrozen(): bool;

    /**
     * @return $this
     */
    public function destroy(): Binding;

    /**
     * @param mixed $targetResource
     * @return $this
     */
    public function trigger($targetResource): Binding;
}
