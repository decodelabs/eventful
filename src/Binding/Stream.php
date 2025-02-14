<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use DecodeLabs\Deliverance\Channel\Stream as StreamChannel;

use Closure;
use DecodeLabs\Eventful\Binding\Io as IoBinding;
use DecodeLabs\Eventful\Binding\IoTrait as IoBindingTrait;
use DecodeLabs\Eventful\BindingTrait;
use DecodeLabs\Eventful\Dispatcher;

class Stream implements IoBinding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }
    use IoBindingTrait;

    public string $type { get => 'Stream'; }

    protected(set) StreamChannel $stream;

    public mixed $ioResource { get => $this->stream->getResource(); }

    protected(set) string $streamId;

    /**
     * Init with timer information
     */
    public function __construct(
        Dispatcher $dispatcher,
        bool $persistent,
        StreamChannel $stream,
        string $ioMode,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ) {
        $this->stream = $stream;
        $this->streamId = (string)spl_object_id($stream);
        $this->ioMode = $ioMode;

        $this->__traitConstruct($dispatcher, $this->ioMode . ':' . $this->streamId, $persistent, $callback);
        $this->timeout = $timeout;

        if($timeoutHandler) {
            $this->timeoutHandler = Closure::fromCallable($timeoutHandler);
        }
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): static
    {
        $this->dispatcher->removeStreamBinding($this);
        return $this;
    }

    /**
     * Trigger event callback
     */
    public function trigger(
        mixed $resource
    ): static {
        if ($this->frozen) {
            return $this;
        }

        ($this->handler)($this->stream, $this);

        if (!$this->persistent) {
            $this->dispatcher->removeStreamBinding($this);
        }

        return $this;
    }

    /**
     * Trigger timeout event callback
     */
    public function triggerTimeout(
        mixed $resource
    ): static {
        if ($this->frozen) {
            return $this;
        }

        if ($this->timeoutHandler) {
            ($this->timeoutHandler)($this->stream, $this);
        }

        return $this;
    }
}
