<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use DecodeLabs\Coercion;

use DecodeLabs\Eventful\Binding;
use DecodeLabs\Eventful\BindingTrait;
use DecodeLabs\Eventful\Dispatcher;
use DecodeLabs\Eventful\Signal as SignalObject;

class Signal implements Binding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }

    public string $type { get => 'Signal'; }

    /**
     * @var array<int,SignalObject>
     */
    protected(set) array $signals = [];

    /**
     * Init with timer information
     *
     * @param iterable<SignalObject|int|string> $signals
     */
    public function __construct(
        Dispatcher $dispatcher,
        string $id,
        bool $persistent,
        iterable $signals,
        callable $callback
    ) {
        $this->__traitConstruct($dispatcher, $id, $persistent, $callback);
        $this->resource = [];

        foreach ($signals as $signal) {
            $signal = SignalObject::create($signal);
            $number = $signal->number;
            $this->signals[$number] = $signal;
            $this->resource[$number] = null;
        }
    }

    /**
     * Has signal registered?
     */
    public function hasSignal(
        int $number
    ): bool {
        return isset($this->signals[$number]);
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): static
    {
        $this->dispatcher->removeSignalBinding($this);
        return $this;
    }

    /**
     * Trigger event callback
     */
    public function trigger(
        mixed $number
    ): static {
        if ($this->frozen) {
            return $this;
        }

        $number = Coercion::toInt($number);
        ($this->handler)($this->signals[$number], $this);

        if (!$this->persistent) {
            $this->dispatcher->removeSignalBinding($this);
        }

        return $this;
    }
}
