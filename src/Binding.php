<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

interface Binding
{
    public function getId(): string;
    public function getType(): string;
    public function isPersistent(): bool;
    public function getHandler(): callable;
    public function getDispatcher(): Dispatcher;

    public function setEventResource($resource): Binding;
    public function getEventResource();

    public function freeze(): Binding;
    public function unfreeze(): Binding;
    public function setFrozen(bool $frozen): Binding;
    public function markFrozen(bool $frozen): Binding;
    public function isFrozen(): bool;
    public function destroy(): Binding;

    public function trigger($targetResource): Binding;
}
