<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use DecodeLabs\Deliverance\Channel\Stream;
use DecodeLabs\Deliverance\Socket;

use DecodeLabs\Eventful\Binding\Signal as SignalBinding;
use DecodeLabs\Eventful\Binding\Socket as SocketBinding;
use DecodeLabs\Eventful\Binding\Stream as StreamBinding;
use DecodeLabs\Eventful\Binding\Timer as TimerBinding;

use DecodeLabs\Exceptional;

trait DispatcherTrait
{
    protected bool $listening = false;

    /**
     * @var callable|null
     */
    protected $cycleHandler;

    protected int $cycles = 0;


    /**
     * @var array<string, SocketBinding>
     */
    protected array $sockets = [];

    /**
     * @var array<string, StreamBinding>
     */
    protected array $streams = [];

    /**
     * @var array<string, SignalBinding>
     */
    protected array $signals = [];

    /**
     * @var array<string, TimerBinding>
     */
    protected array $timers = [];


    /**
     * Has the event loop been started?
     */
    public function isListening(): bool
    {
        return $this->listening;
    }


    /**
     * Freeze all registered bindings
     *
     * @return $this
     */
    public function freezeAllBindings(): static
    {
        $this->freezeAllSockets();
        $this->freezeAllStreams();
        $this->freezeAllSignals();
        $this->freezeAllTimers();

        return $this;
    }

    /**
     * Unfreeze all registered bindings
     *
     * @return $this
     */
    public function unfreezeAllBindings(): static
    {
        $this->unfreezeAllSockets();
        $this->unfreezeAllStreams();
        $this->unfreezeAllSignals();
        $this->unfreezeAllTimers();

        return $this;
    }

    /**
     * Remove all registered bindings
     *
     * @return $this
     */
    public function removeAllBindings(): static
    {
        $this->removeAllSockets();
        $this->removeAllStreams();
        $this->removeAllSignals();
        $this->removeAllTimers();

        return $this;
    }

    /**
     * Get combined list of all bindings
     */
    public function getAllBindings(): array
    {
        return array_merge(
            array_values($this->sockets),
            array_values($this->streams),
            array_values($this->signals),
            array_values($this->timers)
        );
    }

    /**
     * Count all registered bindings
     */
    public function countAllBindings(): int
    {
        return
            count($this->sockets) +
            count($this->streams) +
            count($this->signals) +
            count($this->timers);
    }



    /**
     * Register 1sec timed callback for testing run conditions
     *
     * @return $this
     */
    public function setCycleHandler(?callable $callback = null): static
    {
        $this->cycleHandler = $callback;
        $this->registerCycleHandler($callback);
        return $this;
    }

    /**
     * Get registered cycle callback
     */
    public function getCycleHandler(): ?callable
    {
        return $this->cycleHandler;
    }

    /**
     * Add cycle handler to event loop
     */
    protected function registerCycleHandler(?callable $callback): void
    {
    }



    /**
     * Bind to socket read event
     *
     * @return $this
     */
    public function bindSocketRead(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            true,
            $socket,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to socket read event, frozen
     *
     * @return $this
     */
    public function bindFrozenSocketRead(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            true,
            $socket,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Bind to single socket read event
     *
     * @return $this
     */
    public function bindSocketReadOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            false,
            $socket,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to single socket read event, frozen
     *
     * @return $this
     */
    public function bindFrozenSocketReadOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            false,
            $socket,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Bind to socket write event
     *
     * @return $this
     */
    public function bindSocketWrite(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            true,
            $socket,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to socket write event, frozen
     *
     * @return $this
     */
    public function bindFrozenSocketWrite(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            true,
            $socket,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Bind to single socket write event
     *
     * @return $this
     */
    public function bindSocketWriteOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            false,
            $socket,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to single socket read event, frozen
     *
     * @return $this
     */
    public function bindFrozenSocketWriteOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addSocketBinding(new SocketBinding(
            $this,
            false,
            $socket,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Register a socket binding
     *
     * @return $this
     */
    protected function addSocketBinding(
        SocketBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->getId();

        if (isset($this->sockets[$id])) {
            $this->removeSocketBinding($binding);
        }

        $this->sockets[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerSocketBinding($binding);
        }

        return $this;
    }

    abstract protected function registerSocketBinding(SocketBinding $binding): void;
    abstract protected function unregisterSocketBinding(SocketBinding $binding): void;



    /**
     * Freeze all bindings for socket
     *
     * @return $this
     */
    public function freezeSocket(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $this->freezeBinding($this->sockets['r:' . $id]);
        }

        if (isset($this->sockets['w:' . $id])) {
            $this->freezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * Freeze read bindings for socket
     *
     * @return $this
     */
    public function freezeSocketRead(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $this->freezeBinding($this->sockets['r:' . $id]);
        }

        return $this;
    }

    /**
     * Freeze write bindings for socket
     *
     * @return $this
     */
    public function freezeSocketWrite(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['w:' . $id])) {
            $this->freezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * Freeze all socket bindings
     *
     * @return $this
     */
    public function freezeAllSockets(): static
    {
        foreach ($this->sockets as $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }



    /**
     * Unfreeze all bindings for socket
     *
     * @return $this
     */
    public function unfreezeSocket(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $this->unfreezeBinding($this->sockets['r:' . $id]);
        }

        if (isset($this->sockets['w:' . $id])) {
            $this->unfreezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for socket reads
     *
     * @return $this
     */
    public function unfreezeSocketRead(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $this->unfreezeBinding($this->sockets['r:' . $id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for socket writes
     *
     * @return $this
     */
    public function unfreezeSocketWrite(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['w:' . $id])) {
            $this->unfreezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * Unfreeze all socket bindings
     *
     * @return $this
     */
    public function unfreezeAllSockets(): static
    {
        foreach ($this->sockets as $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }



    /**
     * Remove all bindings for socket
     *
     * @return $this
     */
    public function removeSocket(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $this->removeSocketBinding($this->sockets['r:' . $id]);
        }

        if (isset($this->sockets['w:' . $id])) {
            $this->removeSocketBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * Remove bindings for socket read
     *
     * @return $this
     */
    public function removeSocketRead(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $this->removeSocketBinding($this->sockets['r:' . $id]);
        }

        return $this;
    }

    /**
     * Remove bindings for socket write
     *
     * @return $this
     */
    public function removeSocketWrite(Socket $socket): static
    {
        $id = $socket->getId();

        if (isset($this->sockets['w:' . $id])) {
            $this->removeSocketBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * Remove specific socket binding
     *
     * @return $this
     */
    public function removeSocketBinding(SocketBinding $binding): static
    {
        $this->unregisterSocketBinding($binding);
        unset($this->sockets[$binding->getId()]);

        return $this;
    }

    /**
     * Remove all socket bindings
     *
     * @return $this
     */
    public function removeAllSockets(): static
    {
        foreach ($this->sockets as $id => $binding) {
            $this->unregisterSocketBinding($binding);
            unset($this->sockets[$id]);
        }

        return $this;
    }


    /**
     * Count all socket bindings
     */
    public function countSocketBindings(): int
    {
        return count($this->sockets);
    }

    /**
     * Count all bindings for socket
     */
    public function countSocketBindingsFor(Socket $socket): int
    {
        $count = 0;
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $count++;
        }

        if (isset($this->sockets['w:' . $id])) {
            $count++;
        }

        return $count;
    }

    /**
     * Get all socket bindings
     */
    public function getSocketBindings(): array
    {
        return $this->sockets;
    }

    /**
     * Get all bindings for socket
     */
    public function getSocketBindingsFor(Socket $socket): array
    {
        $output = [];
        $id = $socket->getId();

        if (isset($this->sockets['r:' . $id])) {
            $output['r:' . $id] = $this->sockets['r:' . $id];
        }

        if (isset($this->sockets['w:' . $id])) {
            $output['w:' . $id] = $this->sockets['w:' . $id];
        }

        return $output;
    }

    /**
     * Count all bindings for socket read
     */
    public function countSocketReadBindings(): int
    {
        $count = 0;

        foreach ($this->sockets as $binding) {
            if ($binding->getIoMode() == 'r') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for socket read
     */
    public function getSocketReadBindings(): array
    {
        $output = [];

        foreach ($this->sockets as $id => $binding) {
            if ($binding->getIoMode() == 'r') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    /**
     * Count all bindings for socket write
     */
    public function countSocketWriteBindings(): int
    {
        $count = 0;

        foreach ($this->sockets as $binding) {
            if ($binding->getIoMode() == 'w') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for socket write
     */
    public function getSocketWriteBindings(): array
    {
        $output = [];

        foreach ($this->sockets as $id => $binding) {
            if ($binding->getIoMode() == 'w') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }




    /**
     * Bind to stream read event
     *
     * @return $this
     */
    public function bindStreamRead(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            true,
            $stream,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to stream read event, frozen
     *
     * @return $this
     */
    public function bindFrozenStreamRead(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            true,
            $stream,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Bind to single stream read event
     *
     * @return $this
     */
    public function bindStreamReadOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            false,
            $stream,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to single stream read event, frozen
     *
     * @return $this
     */
    public function bindFrozenStreamReadOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            false,
            $stream,
            'r',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Bind to socket write event
     *
     * @return $this
     */
    public function bindStreamWrite(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            true,
            $stream,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to socket write event, frozen
     *
     * @return $this
     */
    public function bindFrozenStreamWrite(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            true,
            $stream,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Bind to single socket write event
     *
     * @return $this
     */
    public function bindStreamWriteOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            false,
            $stream,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), false);
    }

    /**
     * Bind to single socket read event, frozen
     *
     * @return $this
     */
    public function bindFrozenStreamWriteOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static {
        return $this->addStreamBinding(new StreamBinding(
            $this,
            false,
            $stream,
            'w',
            $callback,
            $timeout,
            $timeoutHandler
        ), true);
    }

    /**
     * Register a stream binding
     *
     * @return $this
     */
    protected function addStreamBinding(
        StreamBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->getId();

        if (isset($this->streams[$id])) {
            $this->removeStreamBinding($binding);
        }

        $this->streams[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerStreamBinding($binding);
        }

        return $this;
    }

    abstract protected function registerStreamBinding(StreamBinding $binding): void;
    abstract protected function unregisterStreamBinding(StreamBinding $binding): void;


    /**
     * Freeze all bindings for stream
     *
     * @return $this
     */
    public function freezeStream(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->freezeBinding($this->streams['r:' . $id]);
        }

        if (isset($this->streams['w:' . $id])) {
            $this->freezeBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
     * Freeze read bindings for stream
     *
     * @return $this
     */
    public function freezeStreamRead(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->freezeBinding($this->streams['r:' . $id]);
        }

        return $this;
    }

    /**
     * Freeze write bindings for stream
     *
     * @return $this
     */
    public function freezeStreamWrite(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:' . $id])) {
            $this->freezeBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
     * Freeze all stream bindings
     *
     * @return $this
     */
    public function freezeAllStreams(): static
    {
        foreach ($this->streams as $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }


    /**
     * Unfreeze all bindings for stream
     *
     * @return $this
     */
    public function unfreezeStream(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->unfreezeBinding($this->streams['r:' . $id]);
        }

        if (isset($this->streams['w:' . $id])) {
            $this->unfreezeBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for stream reads
     *
     * @return $this
     */
    public function unfreezeStreamRead(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->unfreezeBinding($this->streams['r:' . $id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for stream writes
     *
     * @return $this
     */
    public function unfreezeStreamWrite(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:' . $id])) {
            $this->unfreezeBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
     * Unfreeze all stream bindings
     *
     * @return $this
     */
    public function unfreezeAllStreams(): static
    {
        foreach ($this->streams as $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }



    /**
     * Remove all bindings for stream
     *
     * @return $this
     */
    public function removeStream(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->removeStreamBinding($this->streams['r:' . $id]);
        }

        if (isset($this->streams['w:' . $id])) {
            $this->removeStreamBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
     * Remove bindings for stream read
     *
     * @return $this
     */
    public function removeStreamRead(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->removeStreamBinding($this->streams['r:' . $id]);
        }

        return $this;
    }

    /**
     * Remove bindings for stream write
     *
     * @return $this
     */
    public function removeStreamWrite(Stream $stream): static
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:' . $id])) {
            $this->removeStreamBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
     * Remove specific stream binding
     *
     * @return $this
     */
    public function removeStreamBinding(StreamBinding $binding): static
    {
        $this->unregisterStreamBinding($binding);
        unset($this->streams[$binding->getId()]);

        return $this;
    }

    /**
     * Remove all stream bindings
     *
     * @return $this
     */
    public function removeAllStreams(): static
    {
        foreach ($this->streams as $id => $binding) {
            $this->unregisterStreamBinding($binding);
            unset($this->streams[$id]);
        }

        return $this;
    }


    /**
     * Count all stream bindings
     */
    public function countStreamBindings(): int
    {
        return count($this->streams);
    }

    /**
     * Count all bindings for stream
     */
    public function countStreamBindingsFor(Stream $stream): int
    {
        $count = 0;
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $count++;
        }

        if (isset($this->streams['w:' . $id])) {
            $count++;
        }

        return $count;
    }

    /**
     * Get all stream bindings
     */
    public function getStreamBindings(): array
    {
        return $this->streams;
    }

    /**
     * Get all bindings for stream
     */
    public function getStreamBindingsFor(Stream $stream): array
    {
        $output = [];
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $output['r:' . $id] = $this->streams['r:' . $id];
        }

        if (isset($this->streams['w:' . $id])) {
            $output['w:' . $id] = $this->streams['w:' . $id];
        }

        return $output;
    }

    /**
     * Count all bindings for stream read
     */
    public function countStreamReadBindings(): int
    {
        $count = 0;

        foreach ($this->streams as $binding) {
            if ($binding->getIoMode() == 'r') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for stream read
     */
    public function getStreamReadBindings(): array
    {
        $output = [];

        foreach ($this->streams as $id => $binding) {
            if ($binding->getIoMode() == 'r') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    /**
     * Count all bindings for socket write
     */
    public function countStreamWriteBindings(): int
    {
        $count = 0;

        foreach ($this->streams as $binding) {
            if ($binding->getIoMode() == 'w') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for socket write
     */
    public function getStreamWriteBindings(): array
    {
        $output = [];

        foreach ($this->streams as $id => $binding) {
            if ($binding->getIoMode() == 'w') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    /**
     * Get id for stream
     */
    protected function getStreamId(Stream $stream): string
    {
        return (string)spl_object_id($stream);
    }




    /**
     * Bind to signal event
     *
     * @return $this
     */
    public function bindSignal(
        string $id,
        iterable $signals,
        callable $callback
    ): static {
        return $this->addSignalBinding(new SignalBinding(
            $this,
            $id,
            true,
            $signals,
            $callback
        ), false);
    }

    /**
     * Bind to signal event, frozen
     *
     * @return $this
     */
    public function bindFrozenSignal(
        string $id,
        iterable $signals,
        callable $callback
    ): static {
        return $this->addSignalBinding(new SignalBinding(
            $this,
            $id,
            true,
            $signals,
            $callback
        ), true);
    }

    /**
     * Bind to single signal event
     *
     * @return $this
     */
    public function bindSignalOnce(
        string $id,
        iterable $signals,
        callable $callback
    ): static {
        return $this->addSignalBinding(new SignalBinding(
            $this,
            $id,
            false,
            $signals,
            $callback
        ), false);
    }

    /**
     * Bind to single signal event, frozen
     *
     * @return $this
     */
    public function bindFrozenSignalOnce(
        string $id,
        iterable $signals,
        callable $callback
    ): static {
        return $this->addSignalBinding(new SignalBinding(
            $this,
            $id,
            false,
            $signals,
            $callback
        ), true);
    }

    /**
     * Register a signal binding
     *
     * @return $this
     */
    protected function addSignalBinding(
        SignalBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->getId();

        if (isset($this->signals[$id])) {
            $this->removeSignalBinding($binding);
        }

        $this->signals[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerSignalBinding($binding);
        }

        return $this;
    }

    abstract protected function registerSignalBinding(SignalBinding $binding): void;
    abstract protected function unregisterSignalBinding(SignalBinding $binding): void;


    /**
     * Freeze all bindings with signal
     *
     * @return $this
     */
    public function freezeSignal(
        Signal|int|string $signal
    ): static {
        $number = $this->normalizeSignal($signal);

        foreach ($this->signals as $binding) {
            if ($binding->hasSignal($number)) {
                $this->freezeBinding($binding);
            }
        }

        return $this;
    }

    /**
     * Freeze specific signal binding by object or id
     *
     * @return $this
     */
    public function freezeSignalBinding(
        string|SignalBinding $binding
    ): static {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    'Invalid signal binding',
                    null,
                    $orig
                );
            }
        }

        $this->freezeBinding($binding);
        return $this;
    }

    /**
     * Freeze all signal bindings
     *
     * @return $this
     */
    public function freezeAllSignals(): static
    {
        foreach ($this->signals as $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }


    /**
     * Unfreeze all bindings with signal
     *
     * @return $this
     */
    public function unfreezeSignal(
        Signal|int|string $signal
    ): static {
        $number = $this->normalizeSignal($signal);

        foreach ($this->signals as $binding) {
            if ($binding->hasSignal($number)) {
                $this->unfreezeBinding($binding);
            }
        }

        return $this;
    }

    /**
     * Unfreeze specific signal binding by object or id
     *
     * @return $this
     */
    public function unfreezeSignalBinding(
        string|SignalBinding $binding
    ): static {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    'Invalid signal binding',
                    null,
                    $orig
                );
            }
        }

        $this->unfreezeBinding($binding);
        return $this;
    }

    /**
     * Unfreeze all signal bindings
     *
     * @return $this
     */
    public function unfreezeAllSignals(): static
    {
        foreach ($this->signals as $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }


    /**
     * Remove all bindings with signal
     *
     * @return $this
     */
    public function removeSignal(
        Signal|int|string $signal
    ): static {
        $number = $this->normalizeSignal($signal);

        foreach ($this->signals as $binding) {
            if ($binding->hasSignal($number)) {
                $this->removeSignalBinding($binding);
            }
        }

        return $this;
    }

    /**
     * Remove specific signal binding
     *
     * @return $this
     */
    public function removeSignalBinding(
        string|SignalBinding $binding
    ): static {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    'Invalid signal binding',
                    null,
                    $orig
                );
            }
        }

        $id = $binding->getId();
        $this->unregisterSignalBinding($binding);
        unset($this->signals[$id]);

        return $this;
    }

    /**
     * Remove all signal bindings
     *
     * @return $this
     */
    public function removeAllSignals(): static
    {
        foreach ($this->signals as $id => $binding) {
            $this->unregisterSignalBinding($binding);
            unset($this->signals[$id]);
        }

        return $this;
    }



    /**
     * Get signal binding by id or object
     */
    public function getSignalBinding(
        string|SignalBinding $id
    ): ?SignalBinding {
        if ($id instanceof SignalBinding) {
            $id = $id->getId();
        }

        if (!is_string($id)) {
            throw Exceptional::InvalidArgument(
                'Invalid signal id',
                null,
                $id
            );
        }

        return $this->signals[$id] ?? null;
    }

    /**
     * Count all signal bindings
     */
    public function countSignalBindings(): int
    {
        return count($this->signals);
    }

    /**
     * Count bindings with signal
     */
    public function countSignalBindingsFor(
        Signal|int|string $signal
    ): int {
        $count = 0;
        $number = $this->normalizeSignal($signal);

        foreach ($this->signals as $binding) {
            if ($binding->hasSignal($number)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all signal bindings
     */
    public function getSignalBindings(): array
    {
        return $this->signals;
    }

    /**
     * Get bidings with signal
     */
    public function getSignalBindingsFor(
        Signal|int|string $signal
    ): array {
        $output = [];
        $number = $this->normalizeSignal($signal);

        foreach ($this->signals as $id => $binding) {
            if ($binding->hasSignal($number)) {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    /**
     * Normalize signal input
     */
    protected function normalizeSignal(
        Signal|int|string $signal
    ): int {
        return Signal::create($signal)->getNumber();
    }




    /**
     * Bind to a timer event
     *
     * @return $this
     */
    public function bindTimer(
        string $id,
        float $duration,
        callable $callback
    ): static {
        return $this->addTimerBinding(new TimerBinding(
            $this,
            $id,
            true,
            $duration,
            $callback
        ), false);
    }

    /**
     * Bind to a timer event, frozen
     *
     * @return $this
     */
    public function bindFrozenTimer(
        string $id,
        float $duration,
        callable $callback
    ): static {
        return $this->addTimerBinding(new TimerBinding(
            $this,
            $id,
            true,
            $duration,
            $callback
        ), true);
    }

    /**
     * Bind to a single timer event
     *
     * @return $this
     */
    public function bindTimerOnce(
        string $id,
        float $duration,
        callable $callback
    ): static {
        return $this->addTimerBinding(new TimerBinding(
            $this,
            $id,
            false,
            $duration,
            $callback
        ), false);
    }

    /**
     * Bind to a single timer event, frozen
     *
     * @return $this
     */
    public function bindFrozenTimerOnce(
        string $id,
        float $duration,
        callable $callback
    ): static {
        return $this->addTimerBinding(new TimerBinding(
            $this,
            $id,
            false,
            $duration,
            $callback
        ), true);
    }

    /**
     * Register a timing binding
     *
     * @return $this
     */
    protected function addTimerBinding(
        TimerBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->getId();

        if (isset($this->timers[$id])) {
            $this->removeTimer($binding);
        }

        $this->timers[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerTimerBinding($binding);
        }

        return $this;
    }

    abstract protected function registerTimerBinding(TimerBinding $binding): void;
    abstract protected function unregisterTimerBinding(TimerBinding $binding): void;


    /**
     * Freeze timer binding by id
     *
     * @return $this
     */
    public function freezeTimer(
        string|TimerBinding $binding
    ): static {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    'Invalid timer binding',
                    null,
                    $orig
                );
            }
        }

        $this->freezeBinding($binding);
        return $this;
    }

    /**
     * Freeze all timer bindings
     *
     * @return $this
     */
    public function freezeAllTimers(): static
    {
        foreach ($this->timers as $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }


    /**
     * Unfreeze timer binding by id
     *
     * @return $this
     */
    public function unfreezeTimer(
        string|TimerBinding $binding
    ): static {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    'Invalid timer binding',
                    null,
                    $orig
                );
            }
        }

        $this->unfreezeBinding($binding);
        return $this;
    }

    /**
     * Unfreeze all timer bindings
     *
     * @return $this
     */
    public function unfreezeAllTimers(): static
    {
        foreach ($this->timers as $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }


    /**
     * Remove a timer binding by id or object
     *
     * @return $this
     */
    public function removeTimer(
        string|TimerBinding $binding
    ): static {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    'Invalid timer binding',
                    null,
                    $orig
                );
            }
        }

        $id = $binding->getId();
        $this->unregisterTimerBinding($binding);
        unset($this->timers[$id]);

        return $this;
    }

    /**
     * Remove all timer bindings
     *
     * @return $this
     */
    public function removeAllTimers(): static
    {
        foreach ($this->timers as $id => $binding) {
            $this->unregisterTimerBinding($binding);
            unset($this->timers[$id]);
        }

        return $this;
    }


    /**
     * Get signal binding by id or object
     */
    public function getTimerBinding(
        string|TimerBinding $id
    ): ?TimerBinding {
        if ($id instanceof TimerBinding) {
            $id = $id->getId();
        }

        if (!is_string($id)) {
            throw Exceptional::InvalidArgument(
                'Invalid timer id',
                null,
                $id
            );
        }

        return $this->timers[$id] ?? null;
    }

    /**
     * Count all timer bindings
     */
    public function countTimerBindings(): int
    {
        return count($this->timers);
    }

    /**
     * Get all timer bindings
     */
    public function getTimerBindings(): array
    {
        return $this->timers;
    }
}
