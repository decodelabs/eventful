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
    public function listen(): Dispatcher;
    public function isListening(): bool;
    public function stop(): Dispatcher;

    // Global
    public function freezeBinding(Binding $binding): Dispatcher;
    public function unfreezeBinding(Binding $binding): Dispatcher;

    public function freezeAllBindings(): Dispatcher;
    public function unfreezeAllBindings(): Dispatcher;
    public function removeAllBindings(): Dispatcher;

    /**
     * @return array<Binding>
     */
    public function getAllBindings(): array;

    public function countAllBindings(): int;

    public function setCycleHandler(?callable $callback = null): Dispatcher;
    public function getCycleHandler(): ?callable;


    // Socket
    public function bindSocketRead(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenSocketRead(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindSocketReadOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenSocketReadOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindSocketWrite(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenSocketWrite(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;

    public function freezeSocket(Socket $socket): Dispatcher;
    public function freezeSocketRead(Socket $socket): Dispatcher;
    public function freezeSocketWrite(Socket $socket): Dispatcher;
    public function freezeAllSockets(): Dispatcher;

    public function unfreezeSocket(Socket $socket): Dispatcher;
    public function unfreezeSocketRead(Socket $socket): Dispatcher;
    public function unfreezeSocketWrite(Socket $socket): Dispatcher;
    public function unfreezeAllSockets(): Dispatcher;

    public function removeSocket(Socket $socket): Dispatcher;
    public function removeSocketRead(Socket $socket): Dispatcher;
    public function removeSocketWrite(Socket $socket): Dispatcher;
    public function removeSocketBinding(SocketBinding $binding): Dispatcher;
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


    // Stream
    public function bindStreamRead(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenStreamRead(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindStreamReadOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenStreamReadOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindStreamWrite(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenStreamWrite(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;
    public function bindFrozenStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): Dispatcher;

    public function freezeStream(Stream $stream): Dispatcher;
    public function freezeStreamRead(Stream $stream): Dispatcher;
    public function freezeStreamWrite(Stream $stream): Dispatcher;
    public function freezeAllStreams(): Dispatcher;

    public function unfreezeStream(Stream $stream): Dispatcher;
    public function unfreezeStreamRead(Stream $stream): Dispatcher;
    public function unfreezeStreamWrite(Stream $stream): Dispatcher;
    public function unfreezeAllStreams(): Dispatcher;

    public function removeStream(Stream $stream): Dispatcher;
    public function removeStreamRead(Stream $stream): Dispatcher;
    public function removeStreamWrite(Stream $stream): Dispatcher;
    public function removeStreamBinding(StreamBinding $binding): Dispatcher;
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


    // Signal

    /**
     * @param iterable<mixed> $signals
     */
    public function bindSignal(string $id, iterable $signals, callable $callback): Dispatcher;

    /**
     * @param iterable<mixed> $signals
     */
    public function bindFrozenSignal(string $id, iterable $signals, callable $callback): Dispatcher;

    /**
     * @param iterable<mixed> $signals
     */
    public function bindSignalOnce(string $id, iterable $signals, callable $callback): Dispatcher;

    /**
     * @param iterable<mixed> $signals
     */
    public function bindFrozenSignalOnce(string $id, iterable $signals, callable $callback): Dispatcher;

    /**
     * @param mixed $signal
     */
    public function freezeSignal($signal): Dispatcher;

    /**
     * @param string|SignalBinding $binding
     */
    public function freezeSignalBinding($binding): Dispatcher;

    public function freezeAllSignals(): Dispatcher;

    /**
     * @param mixed $signal
     */
    public function unfreezeSignal($signal): Dispatcher;

    /**
     * @param string|SignalBinding $binding
     */
    public function unfreezeSignalBinding($binding): Dispatcher;

    public function unfreezeAllSignals(): Dispatcher;

    /**
     * @param mixed $signal
     */
    public function removeSignal($signal): Dispatcher;

    /**
     * @param string|SignalBinding $binding
     */
    public function removeSignalBinding($binding): Dispatcher;

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


    // Timer
    public function bindTimer(string $id, float $duration, callable $callback): Dispatcher;
    public function bindFrozenTimer(string $id, float $duration, callable $callback): Dispatcher;
    public function bindTimerOnce(string $id, float $duration, callable $callback): Dispatcher;
    public function bindFrozenTimerOnce(string $id, float $duration, callable $callback): Dispatcher;

    /**
     * @param string|TimerBinding $id
     */
    public function freezeTimer($id): Dispatcher;

    public function freezeAllTimers(): Dispatcher;

    /**
     * @param string|TimerBinding $id
     */
    public function unfreezeTimer($id): Dispatcher;

    public function unfreezeAllTimers(): Dispatcher;

    /**
     * @param string|TimerBinding $id
     */
    public function removeTimer($id): Dispatcher;

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
