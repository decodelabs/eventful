<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding {
    use Closure;
    use DecodeLabs\Deliverance\Socket as SocketChannel;
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

        public string $type { get => 'Socket'; }

        protected(set) SocketChannel $socket;

        public mixed $ioResource { get => $this->socket->getResource(); }

        protected(set) string $socketId;

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

            if($timeoutHandler) {
                $this->timeoutHandler = Closure::fromCallable($timeoutHandler);
            }
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
         * Destroy and unregister this binding
         */
        public function destroy(): static
        {
            $this->dispatcher->removeSocketBinding($this);
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

            ($this->handler)($this->socket, $this);

            if (!$this->persistent) {
                $this->dispatcher->removeSocketBinding($this);
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
