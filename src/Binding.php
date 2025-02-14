<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use Closure;

interface Binding
{
    public string $id { get; }
    public string $type { get; }

    public bool $persistent { get; }
    public bool $frozen { get; }

    public mixed $resource { get; set; }
    public Closure $handler { get; }
    public Dispatcher $dispatcher { get; }

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
    public function markFrozen(
        bool $frozen
    ): static;

    public function isFrozen(): bool;

    /**
     * @return $this
     */
    public function destroy(): static;

    /**
     * @return $this
     */
    public function trigger(
        mixed $targetResource
    ): static;
}
