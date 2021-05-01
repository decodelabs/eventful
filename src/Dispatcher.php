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

use DecodeLabs\Systemic\Process\Signal;

interface Dispatcher
{
    /**
     * @return $this
     */
    public function listen(): Dispatcher;

    public function isListening(): bool;

    /**
     * @return $this
     */
    public function stop(): Dispatcher;



    /**
     * @return $this
     */
    public function freezeBinding(Binding $binding): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeBinding(Binding $binding): Dispatcher;


    /**
     * @return $this
     */
    public function freezeAllBindings(): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeAllBindings(): Dispatcher;

    /**
     * @return $this
     */
    public function removeAllBindings(): Dispatcher;

    /**
     * @return array<Binding>
     */
    public function getAllBindings(): array;

    public function countAllBindings(): int;


    /**
     * @return $this
     */
    public function setCycleHandler(?callable $callback = null): Dispatcher;
    public function getCycleHandler(): ?callable;



    /**
     * @return $this
     */
    public function bindSocketRead(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenSocketRead(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindSocketReadOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenSocketReadOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindSocketWrite(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenSocketWrite(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindSocketWriteOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenSocketWriteOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;


    /**
     * @return $this
     */
    public function freezeSocket(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function freezeSocketRead(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function freezeSocketWrite(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function freezeAllSockets(): Dispatcher;


    /**
     * @return $this
     */
    public function unfreezeSocket(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeSocketRead(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeSocketWrite(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeAllSockets(): Dispatcher;


    /**
     * @return $this
     */
    public function removeSocket(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function removeSocketRead(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function removeSocketWrite(Socket $socket): Dispatcher;

    /**
     * @return $this
     */
    public function removeSocketBinding(SocketBinding $binding): Dispatcher;

    /**
     * @return $this
     */
    public function removeAllSockets(): Dispatcher;


    public function countSocketBindings(): int;
    public function countSocketBindingsFor(Socket $socket): int;

    /**
     * @return array<string, Binding>
     */
    public function getSocketBindings(): array;

    /**
     * @return array<string, Binding>
     */
    public function getSocketBindingsFor(Socket $socket): array;

    public function countSocketReadBindings(): int;

    /**
     * @return array<string, Binding>
     */
    public function getSocketReadBindings(): array;

    public function countSocketWriteBindings(): int;

    /**
     * @return array<string, Binding>
     */
    public function getSocketWriteBindings(): array;



    /**
     * @return $this
     */
    public function bindStreamRead(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenStreamRead(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindStreamReadOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenStreamReadOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindStreamWrite(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenStreamWrite(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindStreamWriteOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenStreamWriteOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): Dispatcher;


    /**
     * @return $this
     */
    public function freezeStream(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function freezeStreamRead(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function freezeStreamWrite(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function freezeAllStreams(): Dispatcher;


    /**
     * @return $this
     */
    public function unfreezeStream(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeStreamRead(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeStreamWrite(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeAllStreams(): Dispatcher;


    /**
     * @return $this
     */
    public function removeStream(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function removeStreamRead(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function removeStreamWrite(Stream $stream): Dispatcher;

    /**
     * @return $this
     */
    public function removeStreamBinding(StreamBinding $binding): Dispatcher;

    /**
     * @return $this
     */
    public function removeAllStreams(): Dispatcher;

    public function countStreamBindings(): int;
    public function countStreamBindingsFor(Stream $stream): int;

    /**
     * @return array<string, Binding>
     */
    public function getStreamBindings(): array;

    /**
     * @return array<string, Binding>
     */
    public function getStreamBindingsFor(Stream $stream): array;

    public function countStreamReadBindings(): int;

    /**
     * @return array<string, Binding>
     */
    public function getStreamReadBindings(): array;

    public function countStreamWriteBindings(): int;

    /**
     * @return array<string, Binding>
     */
    public function getStreamWriteBindings(): array;




    /**
     * @param iterable<mixed> $signals
     * @return $this
     */
    public function bindSignal(
        string $id,
        iterable $signals,
        callable $callback
    ): Dispatcher;

    /**
     * @param iterable<mixed> $signals
     * @return $this
     */
    public function bindFrozenSignal(
        string $id,
        iterable $signals,
        callable $callback
    ): Dispatcher;

    /**
     * @param iterable<mixed> $signals
     * @return $this
     */
    public function bindSignalOnce(
        string $id,
        iterable $signals,
        callable $callback
    ): Dispatcher;

    /**
     * @param iterable<mixed> $signals
     * @return $this
     */
    public function bindFrozenSignalOnce(
        string $id,
        iterable $signals,
        callable $callback
    ): Dispatcher;

    /**
     * @param mixed $signal
     * @return $this
     */
    public function freezeSignal($signal): Dispatcher;

    /**
     * @param string|SignalBinding $binding
     * @return $this
     */
    public function freezeSignalBinding($binding): Dispatcher;

    /**
     * @return $this
     */
    public function freezeAllSignals(): Dispatcher;

    /**
     * @param mixed $signal
     * @return $this
     */
    public function unfreezeSignal($signal): Dispatcher;

    /**
     * @param string|SignalBinding $binding
     * @return $this
     */
    public function unfreezeSignalBinding($binding): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeAllSignals(): Dispatcher;

    /**
     * @param mixed $signal
     * @return $this
     */
    public function removeSignal($signal): Dispatcher;

    /**
     * @param string|SignalBinding $binding
     * @return $this
     */
    public function removeSignalBinding($binding): Dispatcher;

    /**
     * @return $this
     */
    public function removeAllSignals(): Dispatcher;

    /**
     * @param string|SignalBinding $id
     */
    public function getSignalBinding($id): ?SignalBinding;

    public function countSignalBindings(): int;

    /**
     * @param mixed $signal
     */
    public function countSignalBindingsFor($signal): int;

    /**
     * @return array<string, Binding>
     */
    public function getSignalBindings(): array;

    /**
     * @param mixed $signal
     * @return array<string, Binding>
     */
    public function getSignalBindingsFor($signal): array;



    /**
     * @return $this
     */
    public function bindTimer(
        string $id,
        float $duration,
        callable $callback
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenTimer(
        string $id,
        float $duration,
        callable $callback
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindTimerOnce(
        string $id,
        float $duration,
        callable $callback
    ): Dispatcher;

    /**
     * @return $this
     */
    public function bindFrozenTimerOnce(
        string $id,
        float $duration,
        callable $callback
    ): Dispatcher;


    /**
     * @param string|TimerBinding $id
     * @return $this
     */
    public function freezeTimer($id): Dispatcher;

    /**
     * @return $this
     */
    public function freezeAllTimers(): Dispatcher;


    /**
     * @param string|TimerBinding $id
     * @return $this
     */
    public function unfreezeTimer($id): Dispatcher;

    /**
     * @return $this
     */
    public function unfreezeAllTimers(): Dispatcher;


    /**
     * @param string|TimerBinding $id
     * @return $this
     */
    public function removeTimer($id): Dispatcher;

    /**
     * @return $this
     */
    public function removeAllTimers(): Dispatcher;


    /**
     * @param string|TimerBinding $id
     */
    public function getTimerBinding($id): ?TimerBinding;

    public function countTimerBindings(): int;

    /**
     * @return array<string, Binding>
     */
    public function getTimerBindings(): array;
}
