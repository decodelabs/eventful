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

use DecodeLabs\Exceptional;
use DecodeLabs\Systemic;

class Signal implements Binding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }

    public $signals = [];

    /**
     * Init with timer information
     */
    public function __construct(
        Dispatcher $dispatcher,
        string $id,
        bool $persistent,
        $signals,
        callable $callback
    ) {
        if (!class_exists(Systemic::class)) {
            throw Exceptional::ComponentUnavailable(
                'Event dispatcher Signal support requires DecodeLabs Systemic'
            );
        }

        $this->__traitConstruct($dispatcher, $id, $persistent, $callback);
        $this->resource = [];

        foreach ((array)$signals as $signal) {
            $signal = Systemic::$process->newSignal($signal);
            $number = $signal->getNumber();
            $this->signals[$number] = $signal;
            $this->resource[$number] = null;
        }
    }

    /**
     * Get binding type
     */
    public function getType(): string
    {
        return 'Signal';
    }

    /**
     * Get signal list
     */
    public function getSignals(): array
    {
        return $this->signals;
    }

    /**
     * Has signal registered?
     */
    public function hasSignal(int $number): bool
    {
        return isset($this->signals[$number]);
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): Binding
    {
        $this->dispatcher->removeSignalBinding($this);
        return $this;
    }

    /**
     * Trigger event callback
     */
    public function trigger($number): Binding
    {
        if ($this->frozen) {
            return $this;
        }

        ($this->handler)($this->signals[$number], $this);

        if (!$this->persistent) {
            $this->dispatcher->removeSignalBinding($this);
        }

        return $this;
    }
}
