<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use DecodeLabs\Eventful\Binding;
use DecodeLabs\Eventful\BindingTrait;
use DecodeLabs\Eventful\Dispatcher;

class Timer implements Binding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }

    public string $type { get => 'Timer'; }

    public protected(set) float $duration;

    public function __construct(
        Dispatcher $dispatcher,
        string $id,
        bool $persistent,
        float $duration,
        callable $callback
    ) {
        $this->__traitConstruct($dispatcher, $id, $persistent, $callback);
        $this->duration = $duration;
    }

    public function destroy(): static
    {
        $this->dispatcher->removeTimer($this);
        return $this;
    }

    public function trigger(
        mixed $time
    ): static {
        if ($this->frozen) {
            return $this;
        }

        ($this->handler)($this);

        if (!$this->persistent) {
            $this->dispatcher->removeTimer($this);
        }

        return $this;
    }
}
