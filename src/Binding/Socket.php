<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding {

use DecodeLabs\Deliverance\Socket as SocketChannel;

use DecodeLabs\Eventful\Binding;
use DecodeLabs\Eventful\Binding\Io as IoBinding;
use DecodeLabs\Eventful\Binding\IoTrait as IoBindingTrait;
use DecodeLabs\Eventful\BindingTrait;
use DecodeLabs\Eventful\Dispatcher;

class Socket implements IoBinding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }
    use IoBindingTrait;

    /**
     * @var SocketChannel
     */
    public $socket;

    /**
     * @var string
     */
    public $socketId;

    /**
     * Init with timer information
     */
    public function __construct(
        Dispatcher $dispatcher,
        bool $persistent,
        SocketChannel $socket,
        string $ioMode,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ) {
        $this->socket = $socket;
        $this->socketId = $socket->getId();
        $this->ioMode = $ioMode;

        $this->__traitConstruct($dispatcher, $this->ioMode . ':' . $this->socketId, $persistent, $callback);
        $this->timeout = $timeout;
        $this->timeoutHandler = $timeoutHandler;
    }

    /**
     * Get binding type
     */
    public function getType(): string
    {
        return 'Socket';
    }

    /**
     * Get socket object
     */
    public function getSocket(): SocketChannel
    {
        return $this->socket;
    }

    /**
     * Is socket stream based?
     */
    public function isStreamBased(): bool
    {
        return true;
        //return $this->socket->isStreamBased();
    }

    /**
     * Get io resource
     */
    public function getIoResource()
    {
        return $this->socket->getResource();
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): Binding
    {
        $this->dispatcher->removeSocketBinding($this);
        return $this;
    }

    /**
     * Trigger event callback
     */
    public function trigger($resource): Binding
    {
        if ($this->frozen) {
            return $this;
        }

        ($this->handler)($this->socket, $this);

        if (!$this->persistent) {
            $this->dispatcher->removeSocketBinding($this);
        }

        return $this;
    }

    /**
     * Trigger timeout event callback
     */
    public function triggerTimeout($resource): IoBinding
    {
        if ($this->frozen) {
            return $this;
        }

        if ($this->timeoutHandler) {
            ($this->timeoutHandler)($this->socket, $this);
        }

        return $this;
    }
}
}

namespace {
    if (!class_exists('Socket')) {
        class Socket
        {
        }
    }
}
