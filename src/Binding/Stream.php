<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use DecodeLabs\Deliverance\Channel\Stream as StreamChannel;

use DecodeLabs\Eventful\Binding;
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

    public StreamChannel $stream;
    public string $streamId;

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
        $this->timeoutHandler = $timeoutHandler;
    }

    /**
     * Get binding type
     */
    public function getType(): string
    {
        return 'Stream';
    }

    /**
     * Get stream object
     */
    public function getStream(): StreamChannel
    {
        return $this->stream;
    }

    /**
     * Get io resource
     */
    public function getIoResource(): mixed
    {
        return $this->stream->getResource();
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
    public function trigger(mixed $resource): static
    {
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
    public function triggerTimeout(mixed $resource): static
    {
        if ($this->frozen) {
            return $this;
        }

        if ($this->timeoutHandler) {
            ($this->timeoutHandler)($this->stream, $this);
        }

        return $this;
    }
}
