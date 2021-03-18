<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful;

use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Atlas\Socket;

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
    public function getSocketBindings(): array;
    public function getSocketBindingsFor(Socket $socket): array;
    public function countSocketReadBindings(): int;
    public function getSocketReadBindings(): array;
    public function countSocketWriteBindings(): int;
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
    public function getStreamBindings(): array;
    public function getStreamBindingsFor(Stream $stream): array;
    public function countStreamReadBindings(): int;
    public function getStreamReadBindings(): array;
    public function countStreamWriteBindings(): int;
    public function getStreamWriteBindings(): array;


    // Signal
    public function bindSignal(string $id, $signals, callable $callback): Dispatcher;
    public function bindFrozenSignal(string $id, $signals, callable $callback): Dispatcher;
    public function bindSignalOnce(string $id, $signals, callable $callback): Dispatcher;
    public function bindFrozenSignalOnce(string $id, $signals, callable $callback): Dispatcher;

    public function freezeSignal($signal): Dispatcher;
    public function freezeSignalBinding($binding): Dispatcher;
    public function freezeAllSignals(): Dispatcher;

    public function unfreezeSignal($signal): Dispatcher;
    public function unfreezeSignalBinding($binding): Dispatcher;
    public function unfreezeAllSignals(): Dispatcher;

    public function removeSignal($signal): Dispatcher;
    public function removeSignalBinding($binding): Dispatcher;
    public function removeAllSignals(): Dispatcher;

    public function getSignalBinding($id): ?SignalBinding;
    public function countSignalBindings(): int;
    public function countSignalBindingsFor($signal): int;
    public function getSignalBindings(): array;
    public function getSignalBindingsFor($signal): array;


    // Timer
    public function bindTimer(string $id, float $duration, callable $callback): Dispatcher;
    public function bindFrozenTimer(string $id, float $duration, callable $callback): Dispatcher;
    public function bindTimerOnce(string $id, float $duration, callable $callback): Dispatcher;
    public function bindFrozenTimerOnce(string $id, float $duration, callable $callback): Dispatcher;

    public function freezeTimer($id): Dispatcher;
    public function freezeAllTimers(): Dispatcher;

    public function unfreezeTimer($id): Dispatcher;
    public function unfreezeAllTimers(): Dispatcher;

    public function removeTimer($id): Dispatcher;
    public function removeAllTimers(): Dispatcher;

    public function getTimerBinding($id): ?TimerBinding;
    public function countTimerBindings(): int;
    public function getTimerBindings(): array;
}
