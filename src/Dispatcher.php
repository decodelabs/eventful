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
use DecodeLabs\Eventful\Signal as SignalObject;

interface Dispatcher
{
    /**
     * @return $this
     */
    public function listen(): static;

    public function isListening(): bool;

    /**
     * @return $this
     */
    public function stop(): static;



    /**
     * @return $this
     */
    public function freezeBinding(Binding $binding): static;

    /**
     * @return $this
     */
    public function unfreezeBinding(Binding $binding): static;


    /**
     * @return $this
     */
    public function freezeAllBindings(): static;

    /**
     * @return $this
     */
    public function unfreezeAllBindings(): static;

    /**
     * @return $this
     */
    public function removeAllBindings(): static;

    /**
     * @return array<Binding>
     */
    public function getAllBindings(): array;

    public function countAllBindings(): int;


    /**
     * @return $this
     */
    public function setCycleHandler(?callable $callback = null): static;
    public function getCycleHandler(): ?callable;



    /**
     * @return $this
     */
    public function bindSocketRead(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenSocketRead(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindSocketReadOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenSocketReadOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindSocketWrite(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenSocketWrite(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindSocketWriteOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenSocketWriteOnce(
        Socket $socket,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;


    /**
     * @return $this
     */
    public function freezeSocket(Socket $socket): static;

    /**
     * @return $this
     */
    public function freezeSocketRead(Socket $socket): static;

    /**
     * @return $this
     */
    public function freezeSocketWrite(Socket $socket): static;

    /**
     * @return $this
     */
    public function freezeAllSockets(): static;


    /**
     * @return $this
     */
    public function unfreezeSocket(Socket $socket): static;

    /**
     * @return $this
     */
    public function unfreezeSocketRead(Socket $socket): static;

    /**
     * @return $this
     */
    public function unfreezeSocketWrite(Socket $socket): static;

    /**
     * @return $this
     */
    public function unfreezeAllSockets(): static;


    /**
     * @return $this
     */
    public function removeSocket(Socket $socket): static;

    /**
     * @return $this
     */
    public function removeSocketRead(Socket $socket): static;

    /**
     * @return $this
     */
    public function removeSocketWrite(Socket $socket): static;

    /**
     * @return $this
     */
    public function removeSocketBinding(SocketBinding $binding): static;

    /**
     * @return $this
     */
    public function removeAllSockets(): static;


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
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenStreamRead(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindStreamReadOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenStreamReadOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindStreamWrite(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenStreamWrite(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindStreamWriteOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenStreamWriteOnce(
        Stream $stream,
        callable $callback,
        ?float $timeout = null,
        ?callable $timeoutHandler = null
    ): static;


    /**
     * @return $this
     */
    public function freezeStream(Stream $stream): static;

    /**
     * @return $this
     */
    public function freezeStreamRead(Stream $stream): static;

    /**
     * @return $this
     */
    public function freezeStreamWrite(Stream $stream): static;

    /**
     * @return $this
     */
    public function freezeAllStreams(): static;


    /**
     * @return $this
     */
    public function unfreezeStream(Stream $stream): static;

    /**
     * @return $this
     */
    public function unfreezeStreamRead(Stream $stream): static;

    /**
     * @return $this
     */
    public function unfreezeStreamWrite(Stream $stream): static;

    /**
     * @return $this
     */
    public function unfreezeAllStreams(): static;


    /**
     * @return $this
     */
    public function removeStream(Stream $stream): static;

    /**
     * @return $this
     */
    public function removeStreamRead(Stream $stream): static;

    /**
     * @return $this
     */
    public function removeStreamWrite(Stream $stream): static;

    /**
     * @return $this
     */
    public function removeStreamBinding(StreamBinding $binding): static;

    /**
     * @return $this
     */
    public function removeAllStreams(): static;

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
     * @param iterable<SignalObject|int|string> $signals
     * @return $this
     */
    public function bindSignal(
        string $id,
        iterable $signals,
        callable $callback
    ): static;

    /**
     * @param iterable<SignalObject|int|string> $signals
     * @return $this
     */
    public function bindFrozenSignal(
        string $id,
        iterable $signals,
        callable $callback
    ): static;

    /**
     * @param iterable<SignalObject|int|string> $signals
     * @return $this
     */
    public function bindSignalOnce(
        string $id,
        iterable $signals,
        callable $callback
    ): static;

    /**
     * @param iterable<SignalObject|int|string> $signals
     * @return $this
     */
    public function bindFrozenSignalOnce(
        string $id,
        iterable $signals,
        callable $callback
    ): static;

    /**
     * @return $this
     */
    public function freezeSignal(
        SignalObject|int|string $signal
    ): static;

    /**
     * @return $this
     */
    public function freezeSignalBinding(
        string|SignalBinding $binding
    ): static;

    /**
     * @return $this
     */
    public function freezeAllSignals(): static;

    /**
     * @return $this
     */
    public function unfreezeSignal(
        SignalObject|int|string $signal
    ): static;

    /**
     * @return $this
     */
    public function unfreezeSignalBinding(
        string|SignalBinding $binding
    ): static;

    /**
     * @return $this
     */
    public function unfreezeAllSignals(): static;

    /**
     * @return $this
     */
    public function removeSignal(
        SignalObject|int|string $signal
    ): static;

    /**
     * @return $this
     */
    public function removeSignalBinding(
        string|SignalBinding $binding
    ): static;

    /**
     * @return $this
     */
    public function removeAllSignals(): static;

    public function getSignalBinding(
        string|SignalBinding $id
    ): ?SignalBinding;

    public function countSignalBindings(): int;

    public function countSignalBindingsFor(
        SignalObject|int|string $signal
    ): int;

    /**
     * @return array<string, Binding>
     */
    public function getSignalBindings(): array;

    /**
     * @return array<string, Binding>
     */
    public function getSignalBindingsFor(
        SignalObject|int|string $signal
    ): array;



    /**
     * @return $this
     */
    public function bindTimer(
        string $id,
        float $duration,
        callable $callback
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenTimer(
        string $id,
        float $duration,
        callable $callback
    ): static;

    /**
     * @return $this
     */
    public function bindTimerOnce(
        string $id,
        float $duration,
        callable $callback
    ): static;

    /**
     * @return $this
     */
    public function bindFrozenTimerOnce(
        string $id,
        float $duration,
        callable $callback
    ): static;


    /**
     * @return $this
     */
    public function freezeTimer(
        string|TimerBinding $id
    ): static;

    /**
     * @return $this
     */
    public function freezeAllTimers(): static;


    /**
     * @return $this
     */
    public function unfreezeTimer(
        string|TimerBinding $id
    ): static;

    /**
     * @return $this
     */
    public function unfreezeAllTimers(): static;


    /**
     * @return $this
     */
    public function removeTimer(
        string|TimerBinding $id
    ): static;

    /**
     * @return $this
     */
    public function removeAllTimers(): static;


    public function getTimerBinding(
        string|TimerBinding $id
    ): ?TimerBinding;

    public function countTimerBindings(): int;

    /**
     * @return array<string, Binding>
     */
    public function getTimerBindings(): array;
}
