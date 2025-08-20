<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use Closure;
use DecodeLabs\Deliverance\Channel\Stream;
use DecodeLabs\Deliverance\Socket;
use DecodeLabs\Eventful\Binding\Signal as SignalBinding;
use DecodeLabs\Eventful\Binding\Socket as SocketBinding;
use DecodeLabs\Eventful\Binding\Stream as StreamBinding;
use DecodeLabs\Eventful\Binding\Timer as TimerBinding;
use DecodeLabs\Exceptional;

/**
 * @phpstan-require-implements Dispatcher
 */
trait DispatcherTrait
{
    public protected(set) bool $listening = false;
    public protected(set) ?Closure $cycleHandler = null;
    public protected(set) ?Closure $tickHandler = null;
    public protected(set) int $cycles = 0;


    /**
     * @var array<string,SocketBinding>
     */
    protected array $sockets = [];

    /**
     * @var array<string,StreamBinding>
     */
    protected array $streams = [];

    /**
     * @var array<string,SignalBinding>
     */
    protected array $signals = [];

    /**
     * @var array<string,TimerBinding>
     */
    protected array $timers = [];



    public function isListening(): bool
    {
        return $this->listening;
    }


    /**
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

    public function getAllBindings(): array
    {
        return array_merge(
            array_values($this->sockets),
            array_values($this->streams),
            array_values($this->signals),
            array_values($this->timers)
        );
    }

    public function countAllBindings(): int
    {
        return
            count($this->sockets) +
            count($this->streams) +
            count($this->signals) +
            count($this->timers);
    }



    /**
     * @return $this
     */
    public function setCycleHandler(
        ?callable $callback = null
    ): static {
        $this->cycleHandler = $callback ? Closure::fromCallable($callback) : null;
        $this->registerCycleHandler($callback);
        return $this;
    }

    public function getCycleHandler(): ?Closure
    {
        return $this->cycleHandler;
    }


    protected function registerCycleHandler(
        ?callable $callback
    ): void {
    }


    /**
     * @return $this
     */
    public function setTickHandler(
        ?callable $callback = null
    ): static {
        $this->tickHandler = $callback ? Closure::fromCallable($callback) : null;
        $this->registerTickHandler($callback);
        return $this;
    }

    public function getTickHandler(): ?Closure
    {
        return $this->tickHandler;
    }

    protected function registerTickHandler(
        ?callable $callback
    ): void {
    }






    /**
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
     * @return $this
     */
    protected function addSocketBinding(
        SocketBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->id;

        if (isset($this->sockets[$id])) {
            $this->removeSocketBinding($binding);
        }

        $this->sockets[$id] = $binding;

        if ($frozen) {
            $binding->freeze();
        } else {
            $this->registerSocketBinding($binding);
        }

        return $this;
    }

    abstract protected function registerSocketBinding(SocketBinding $binding): void;
    abstract protected function unregisterSocketBinding(SocketBinding $binding): void;



    /**
     * @return $this
     */
    public function freezeSocket(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $this->freezeBinding($this->sockets['r:' . $id]);
        }

        if (isset($this->sockets['w:' . $id])) {
            $this->freezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function freezeSocketRead(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $this->freezeBinding($this->sockets['r:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function freezeSocketWrite(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['w:' . $id])) {
            $this->freezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
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
     * @return $this
     */
    public function unfreezeSocket(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $this->unfreezeBinding($this->sockets['r:' . $id]);
        }

        if (isset($this->sockets['w:' . $id])) {
            $this->unfreezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function unfreezeSocketRead(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $this->unfreezeBinding($this->sockets['r:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function unfreezeSocketWrite(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['w:' . $id])) {
            $this->unfreezeBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
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
     * @return $this
     */
    public function removeSocket(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $this->removeSocketBinding($this->sockets['r:' . $id]);
        }

        if (isset($this->sockets['w:' . $id])) {
            $this->removeSocketBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeSocketRead(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $this->removeSocketBinding($this->sockets['r:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeSocketWrite(
        Socket $socket
    ): static {
        $id = $socket->id;

        if (isset($this->sockets['w:' . $id])) {
            $this->removeSocketBinding($this->sockets['w:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeSocketBinding(
        SocketBinding $binding
    ): static {
        $this->unregisterSocketBinding($binding);
        unset($this->sockets[$binding->id]);

        return $this;
    }

    /**
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


    public function countSocketBindings(): int
    {
        return count($this->sockets);
    }

    public function countSocketBindingsFor(
        Socket $socket
    ): int {
        $count = 0;
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $count++;
        }

        if (isset($this->sockets['w:' . $id])) {
            $count++;
        }

        return $count;
    }

    public function getSocketBindings(): array
    {
        return $this->sockets;
    }

    public function getSocketBindingsFor(
        Socket $socket
    ): array {
        $output = [];
        $id = $socket->id;

        if (isset($this->sockets['r:' . $id])) {
            $output['r:' . $id] = $this->sockets['r:' . $id];
        }

        if (isset($this->sockets['w:' . $id])) {
            $output['w:' . $id] = $this->sockets['w:' . $id];
        }

        return $output;
    }

    public function countSocketReadBindings(): int
    {
        $count = 0;

        foreach ($this->sockets as $binding) {
            if ($binding->ioMode == 'r') {
                $count++;
            }
        }

        return $count;
    }

    public function getSocketReadBindings(): array
    {
        $output = [];

        foreach ($this->sockets as $id => $binding) {
            if ($binding->ioMode == 'r') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    public function countSocketWriteBindings(): int
    {
        $count = 0;

        foreach ($this->sockets as $binding) {
            if ($binding->ioMode == 'w') {
                $count++;
            }
        }

        return $count;
    }

    public function getSocketWriteBindings(): array
    {
        $output = [];

        foreach ($this->sockets as $id => $binding) {
            if ($binding->ioMode == 'w') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }




    /**
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
     * @return $this
     */
    protected function addStreamBinding(
        StreamBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->id;

        if (isset($this->streams[$id])) {
            $this->removeStreamBinding($binding);
        }

        $this->streams[$id] = $binding;

        if ($frozen) {
            $binding->freeze();
        } else {
            $this->registerStreamBinding($binding);
        }

        return $this;
    }

    abstract protected function registerStreamBinding(
        StreamBinding $binding
    ): void;

    abstract protected function unregisterStreamBinding(
        StreamBinding $binding
    ): void;



    /**
     * @return $this
     */
    public function freezeStream(
        Stream $stream
    ): static {
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
    public function freezeStreamRead(
        Stream $stream
    ): static {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->freezeBinding($this->streams['r:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function freezeStreamWrite(
        Stream $stream
    ): static {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:' . $id])) {
            $this->freezeBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
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
     * @return $this
     */
    public function unfreezeStream(
        Stream $stream
    ): static {
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
     * @return $this
     */
    public function unfreezeStreamRead(
        Stream $stream
    ): static {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->unfreezeBinding($this->streams['r:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function unfreezeStreamWrite(
        Stream $stream
    ): static {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:' . $id])) {
            $this->unfreezeBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
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
     * @return $this
     */
    public function removeStream(
        Stream $stream
    ): static {
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
     * @return $this
     */
    public function removeStreamRead(
        Stream $stream
    ): static {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:' . $id])) {
            $this->removeStreamBinding($this->streams['r:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeStreamWrite(
        Stream $stream
    ): static {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:' . $id])) {
            $this->removeStreamBinding($this->streams['w:' . $id]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeStreamBinding(
        StreamBinding $binding
    ): static {
        $this->unregisterStreamBinding($binding);
        unset($this->streams[$binding->id]);

        return $this;
    }

    /**
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



    public function countStreamBindings(): int
    {
        return count($this->streams);
    }


    public function countStreamBindingsFor(
        Stream $stream
    ): int {
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


    public function getStreamBindings(): array
    {
        return $this->streams;
    }


    public function getStreamBindingsFor(
        Stream $stream
    ): array {
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


    public function countStreamReadBindings(): int
    {
        $count = 0;

        foreach ($this->streams as $binding) {
            if ($binding->ioMode == 'r') {
                $count++;
            }
        }

        return $count;
    }


    public function getStreamReadBindings(): array
    {
        $output = [];

        foreach ($this->streams as $id => $binding) {
            if ($binding->ioMode == 'r') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }


    public function countStreamWriteBindings(): int
    {
        $count = 0;

        foreach ($this->streams as $binding) {
            if ($binding->ioMode == 'w') {
                $count++;
            }
        }

        return $count;
    }


    public function getStreamWriteBindings(): array
    {
        $output = [];

        foreach ($this->streams as $id => $binding) {
            if ($binding->ioMode == 'w') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }


    protected function getStreamId(
        Stream $stream
    ): string {
        return (string)spl_object_id($stream);
    }




    /**
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
     * @return $this
     */
    protected function addSignalBinding(
        SignalBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->id;

        if (isset($this->signals[$id])) {
            $this->removeSignalBinding($binding);
        }

        $this->signals[$id] = $binding;

        if ($frozen) {
            $binding->freeze();
        } else {
            $this->registerSignalBinding($binding);
        }

        return $this;
    }

    abstract protected function registerSignalBinding(
        SignalBinding $binding
    ): void;

    abstract protected function unregisterSignalBinding(
        SignalBinding $binding
    ): void;



    /**
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
     * @return $this
     */
    public function freezeSignalBinding(
        string|SignalBinding $binding
    ): static {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    message: 'Invalid signal binding',
                    data: $orig
                );
            }
        }

        $this->freezeBinding($binding);
        return $this;
    }

    /**
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
     * @return $this
     */
    public function unfreezeSignalBinding(
        string|SignalBinding $binding
    ): static {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    message: 'Invalid signal binding',
                    data: $orig
                );
            }
        }

        $this->unfreezeBinding($binding);
        return $this;
    }

    /**
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
     * @return $this
     */
    public function removeSignalBinding(
        string|SignalBinding $binding
    ): static {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    message: 'Invalid signal binding',
                    data: $orig
                );
            }
        }

        $id = $binding->id;
        $this->unregisterSignalBinding($binding);
        unset($this->signals[$id]);

        return $this;
    }

    /**
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



    public function getSignalBinding(
        string|SignalBinding $id
    ): ?SignalBinding {
        if ($id instanceof SignalBinding) {
            $id = $id->id;
        }

        return $this->signals[$id] ?? null;
    }


    public function countSignalBindings(): int
    {
        return count($this->signals);
    }


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


    public function getSignalBindings(): array
    {
        return $this->signals;
    }


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


    protected function normalizeSignal(
        Signal|int|string $signal
    ): int {
        return Signal::create($signal)->number;
    }




    /**
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
     * @return $this
     */
    protected function addTimerBinding(
        TimerBinding $binding,
        bool $frozen
    ): static {
        $id = $binding->id;

        if (isset($this->timers[$id])) {
            $this->removeTimer($binding);
        }

        $this->timers[$id] = $binding;

        if ($frozen) {
            $binding->freeze();
        } else {
            $this->registerTimerBinding($binding);
        }

        return $this;
    }

    abstract protected function registerTimerBinding(
        TimerBinding $binding
    ): void;

    abstract protected function unregisterTimerBinding(
        TimerBinding $binding
    ): void;



    /**
     * @return $this
     */
    public function freezeTimer(
        string|TimerBinding $binding
    ): static {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    message: 'Invalid timer binding',
                    data: $orig
                );
            }
        }

        $this->freezeBinding($binding);
        return $this;
    }

    /**
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
     * @return $this
     */
    public function unfreezeTimer(
        string|TimerBinding $binding
    ): static {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    message: 'Invalid timer binding',
                    data: $orig
                );
            }
        }

        $this->unfreezeBinding($binding);
        return $this;
    }

    /**
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
     * @return $this
     */
    public function removeTimer(
        string|TimerBinding $binding
    ): static {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Exceptional::InvalidArgument(
                    message: 'Invalid timer binding',
                    data: $orig
                );
            }
        }

        $id = $binding->id;
        $this->unregisterTimerBinding($binding);
        unset($this->timers[$id]);

        return $this;
    }

    /**
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


    public function getTimerBinding(
        string|TimerBinding $id
    ): ?TimerBinding {
        if ($id instanceof TimerBinding) {
            $id = $id->id;
        }

        return $this->timers[$id] ?? null;
    }


    public function countTimerBindings(): int
    {
        return count($this->timers);
    }


    public function getTimerBindings(): array
    {
        return $this->timers;
    }
}
