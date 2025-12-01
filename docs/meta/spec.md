# Eventful — Package Specification

> **Cluster:** `io`
> **Language:** `php`
> **Milestone:** `m2`
> **Repo:** `https://github.com/decodelabs/eventful`
> **Role:** IO event loop

This document describes the purpose, contracts, and design of **Eventful** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Eventful in their own applications or libraries.
- Contributors **maintaining or extending** Eventful.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Eventful provides an extensible asynchronous IO event dispatcher for PHP. It enables interactive and asynchronous processes by listening for events on IO (streams and sockets), signals, and timers, and responding accordingly. It automatically uses PHP's Event extension if available for faster and more reliable event loops, otherwise falling back to a basic `select()` loop. It provides a unified API for binding callbacks to various event sources, managing binding lifecycle (freeze/unfreeze, persistent/one-time), handling timeouts, and coordinating event loop execution with cycle and tick handlers.

### 1.2 Non-Goals

Eventful does **not**:

- Provide HTTP server functionality — it's an event loop library
- Handle process management — it's an event dispatcher
- Provide networking protocols — it uses Deliverance for IO
- Handle file system watching — it's focused on IO events
- Provide coroutines or async/await — it's callback-based
- Handle event bus/pub-sub — it's an IO event loop
- Provide worker pools — it's a single-threaded event loop
- Handle database connections — it's focused on IO streams

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `io` (see Chorus taxonomy)
- Eventful is an IO package that provides asynchronous event loop functionality in the Decode Labs ecosystem. It sits in the IO cluster alongside Deliverance (which provides the IO channels it monitors). It depends on Coercion, Deliverance, and Exceptional. It's used by Systemic for process management. It provides the foundation for asynchronous IO operations across the ecosystem.

### 2.2 Typical Usage Contexts

Typical places Eventful appears:

- Interactive CLI applications
- Asynchronous IO operations
- Signal handling in long-running processes
- Timer-based operations
- Network socket monitoring
- Stream-based data processing
- Event-driven architectures
- Process coordination

Eventful is intended to be used whenever code needs to handle multiple IO sources, signals, or timers in a non-blocking, event-driven manner.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Eventful\Dispatcher`
  Interface for event dispatchers. Defines methods for listening, stopping, binding events (sockets, streams, signals, timers), freezing/unfreezing bindings, managing binding lifecycle, and setting cycle/tick handlers.

- `DecodeLabs\Eventful\DispatcherTrait`
  Trait providing common dispatcher functionality. Implements binding management, freeze/unfreeze operations, and binding query methods.

- `DecodeLabs\Eventful\Dispatcher\Event`
  Event extension-based dispatcher implementation. Uses PHP's `ext-event` for high-performance event loops. Automatically selected by Factory when extension is available.

- `DecodeLabs\Eventful\Dispatcher\Select`
  Select-based dispatcher implementation. Uses `socket_select()` and `stream_select()` for event loops. Fallback when Event extension is not available.

- `DecodeLabs\Eventful\Factory`
  Factory for creating dispatcher instances. Automatically selects Event dispatcher if extension is loaded, otherwise Select dispatcher.

- `DecodeLabs\Eventful\Provider`
  Interface for objects that provide event dispatcher access. Defines `eventDispatcher` property and `isRunning()` method.

- `DecodeLabs\Eventful\ProviderTrait`
  Trait providing Provider implementation. Manages event dispatcher property with validation.

- `DecodeLabs\Eventful\Binding`
  Interface for event bindings. Defines properties for ID, type, persistence, frozen state, resource, handler, and dispatcher. Provides methods for freeze/unfreeze, destroy, and trigger.

- `DecodeLabs\Eventful\BindingTrait`
  Trait providing common binding functionality. Implements freeze/unfreeze operations and binding state management.

- `DecodeLabs\Eventful\Binding\Io`
  Interface for IO-based bindings (sockets and streams). Extends Binding and adds properties for IO mode, timeout, timeout handler, and IO resource.

- `DecodeLabs\Eventful\Binding\IoTrait`
  Trait providing IO binding functionality. Implements IO mode, timeout, and timeout handler properties.

- `DecodeLabs\Eventful\Binding\Socket`
  Socket binding implementation. Binds callbacks to Deliverance Socket channels for read/write events.

- `DecodeLabs\Eventful\Binding\Stream`
  Stream binding implementation. Binds callbacks to Deliverance Stream channels for read/write events.

- `DecodeLabs\Eventful\Binding\Signal`
  Signal binding implementation. Binds callbacks to system signals (SIGINT, SIGTERM, etc.).

- `DecodeLabs\Eventful\Binding\Timer`
  Timer binding implementation. Binds callbacks to periodic or one-time timers.

- `DecodeLabs\Eventful\Signal`
  Signal representation class. Provides signal name/number conversion and normalization. Supports signal constants and numeric identifiers.

### 3.2 Main Entry Points

The main usage pattern is through Factory:

```php
use DecodeLabs\Eventful\Factory;

$dispatcher = Factory::newDispatcher()
    ->bindTimer('timer1', 2, function() {
        // Timer callback
    })
    ->bindStreamRead($stream, function($stream, $binding) {
        // Stream read callback
    })
    ->listen();
```

---

## 4. Dependencies

### 4.1 Decode Labs

- `decodelabs/coercion` (required)
  Used for type coercion when converting signal identifiers and resource IDs.

- `decodelabs/deliverance` (required)
  Used for IO channels (Stream and Socket) that Eventful monitors. Eventful binds to Deliverance channels for read/write events.

- `decodelabs/exceptional` (required)
  Used for exception handling throughout the library.

### 4.2 External

- `ext-event` (optional, suggested)
  PHP Event extension for faster and more reliable event loops. If available, Factory automatically uses Event dispatcher. If not available, falls back to Select dispatcher using `socket_select()` and `stream_select()`.

- `ext-pcntl` (optional)
  PHP PCNTL extension for signal handling. If available, Select dispatcher can handle signals. If not available, signal handling is limited.

### 4.3 Optional Integrations

- `ext-event` — Detected at runtime if installed, used for high-performance event loops via Event dispatcher.
- `ext-pcntl` — Detected at runtime if installed, used for signal handling in Select dispatcher.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Dispatcher can only listen once at a time
- Bindings are uniquely identified by ID
- Frozen bindings do not trigger callbacks
- Persistent bindings remain active after triggering
- One-time bindings are removed after triggering
- Timer durations are in seconds (float)
- IO timeouts are in seconds (float)
- Cycle handler is called approximately once per second
- Tick handler is called frequently (every 0.01 seconds in Event dispatcher)
- Cycle handler can return false to stop the loop
- Tick handler can return false to stop the loop
- Signal bindings can handle multiple signals
- Socket bindings use socket ID for identification
- Stream bindings use object ID for identification
- Timer bindings use string ID for identification
- Signal bindings use string ID for identification
- Event dispatcher uses Event extension if available
- Select dispatcher uses select() functions as fallback
- Binding resources are managed by dispatcher implementation
- Binding handlers receive binding instance as second parameter
- IO bindings receive channel as first parameter
- Signal bindings receive Signal object as first parameter
- Timer bindings receive Timer binding as first parameter

### 5.2 Input & Output Contracts

**Dispatcher Operations:**
- `listen(): static` — Starts event loop (blocks until stopped)
- `stop(): static` — Stops event loop
- `isListening(): bool` — Checks if loop is running
- `freezeBinding(Binding $binding): static` — Freezes a binding (prevents triggering)
- `unfreezeBinding(Binding $binding): static` — Unfreezes a binding (allows triggering)
- `freezeAllBindings(): static` — Freezes all bindings
- `unfreezeAllBindings(): static` — Unfreezes all bindings
- `removeAllBindings(): static` — Removes all bindings
- `getAllBindings(): array<Binding>` — Gets all bindings
- `countAllBindings(): int` — Counts all bindings
- `setCycleHandler(?callable $callback): static` — Sets cycle handler (called ~1/second)
- `getCycleHandler(): ?Closure` — Gets cycle handler
- `setTickHandler(?callable $callback): static` — Sets tick handler (called frequently)
- `getTickHandler(): ?Closure` — Gets tick handler

**Socket Binding Operations:**
- `bindSocketRead(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds read callback to socket
- `bindFrozenSocketRead(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen read callback to socket
- `bindSocketReadOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds one-time read callback to socket
- `bindFrozenSocketReadOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen one-time read callback to socket
- `bindSocketWrite(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds write callback to socket
- `bindFrozenSocketWrite(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen write callback to socket
- `bindSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds one-time write callback to socket
- `bindFrozenSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen one-time write callback to socket
- `freezeSocket(Socket $socket): static` — Freezes all bindings for socket
- `freezeSocketRead(Socket $socket): static` — Freezes read bindings for socket
- `freezeSocketWrite(Socket $socket): static` — Freezes write bindings for socket
- `freezeAllSockets(): static` — Freezes all socket bindings
- `unfreezeSocket(Socket $socket): static` — Unfreezes all bindings for socket
- `unfreezeSocketRead(Socket $socket): static` — Unfreezes read bindings for socket
- `unfreezeSocketWrite(Socket $socket): static` — Unfreezes write bindings for socket
- `unfreezeAllSockets(): static` — Unfreezes all socket bindings
- `removeSocket(Socket $socket): static` — Removes all bindings for socket
- `removeSocketRead(Socket $socket): static` — Removes read bindings for socket
- `removeSocketWrite(Socket $socket): static` — Removes write bindings for socket
- `removeSocketBinding(SocketBinding $binding): static` — Removes specific socket binding
- `removeAllSockets(): static` — Removes all socket bindings
- `countSocketBindings(): int` — Counts socket bindings
- `countSocketBindingsFor(Socket $socket): int` — Counts bindings for socket
- `getSocketBindings(): array<string,Binding>` — Gets all socket bindings
- `getSocketBindingsFor(Socket $socket): array<string,Binding>` — Gets bindings for socket
- `countSocketReadBindings(): int` — Counts read socket bindings
- `getSocketReadBindings(): array<string,Binding>` — Gets read socket bindings
- `countSocketWriteBindings(): int` — Counts write socket bindings
- `getSocketWriteBindings(): array<string,Binding>` — Gets write socket bindings

**Stream Binding Operations:**
- `bindStreamRead(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds read callback to stream
- `bindFrozenStreamRead(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen read callback to stream
- `bindStreamReadOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds one-time read callback to stream
- `bindFrozenStreamReadOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen one-time read callback to stream
- `bindStreamWrite(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds write callback to stream
- `bindFrozenStreamWrite(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen write callback to stream
- `bindStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds one-time write callback to stream
- `bindFrozenStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): static` — Binds frozen one-time write callback to stream
- `freezeStream(Stream $stream): static` — Freezes all bindings for stream
- `freezeStreamRead(Stream $stream): static` — Freezes read bindings for stream
- `freezeStreamWrite(Stream $stream): static` — Freezes write bindings for stream
- `freezeAllStreams(): static` — Freezes all stream bindings
- `unfreezeStream(Stream $stream): static` — Unfreezes all bindings for stream
- `unfreezeStreamRead(Stream $stream): static` — Unfreezes read bindings for stream
- `unfreezeStreamWrite(Stream $stream): static` — Unfreezes write bindings for stream
- `unfreezeAllStreams(): static` — Unfreezes all stream bindings
- `removeStream(Stream $stream): static` — Removes all bindings for stream
- `removeStreamRead(Stream $stream): static` — Removes read bindings for stream
- `removeStreamWrite(Stream $stream): static` — Removes write bindings for stream
- `removeStreamBinding(StreamBinding $binding): static` — Removes specific stream binding
- `removeAllStreams(): static` — Removes all stream bindings
- `countStreamBindings(): int` — Counts stream bindings
- `countStreamBindingsFor(Stream $stream): int` — Counts bindings for stream
- `getStreamBindings(): array<string,Binding>` — Gets all stream bindings
- `getStreamBindingsFor(Stream $stream): array<string,Binding>` — Gets bindings for stream
- `countStreamReadBindings(): int` — Counts read stream bindings
- `getStreamReadBindings(): array<string,Binding>` — Gets read stream bindings
- `countStreamWriteBindings(): int` — Counts write stream bindings
- `getStreamWriteBindings(): array<string,Binding>` — Gets write stream bindings

**Signal Binding Operations:**
- `bindSignal(string $id, iterable<Signal|int|string> $signals, callable $callback): static` — Binds callback to signals
- `bindFrozenSignal(string $id, iterable<Signal|int|string> $signals, callable $callback): static` — Binds frozen callback to signals
- `bindSignalOnce(string $id, iterable<Signal|int|string> $signals, callable $callback): static` — Binds one-time callback to signals
- `bindFrozenSignalOnce(string $id, iterable<Signal|int|string> $signals, callable $callback): static` — Binds frozen one-time callback to signals
- `freezeSignal(Signal|int|string $signal): static` — Freezes bindings for signal
- `freezeSignalBinding(string|SignalBinding $binding): static` — Freezes specific signal binding
- `freezeAllSignals(): static` — Freezes all signal bindings
- `unfreezeSignal(Signal|int|string $signal): static` — Unfreezes bindings for signal
- `unfreezeSignalBinding(string|SignalBinding $binding): static` — Unfreezes specific signal binding
- `unfreezeAllSignals(): static` — Unfreezes all signal bindings
- `removeSignal(Signal|int|string $signal): static` — Removes bindings for signal
- `removeSignalBinding(string|SignalBinding $binding): static` — Removes specific signal binding
- `removeAllSignals(): static` — Removes all signal bindings
- `getSignalBinding(string|SignalBinding $id): ?SignalBinding` — Gets signal binding by ID
- `countSignalBindings(): int` — Counts signal bindings
- `countSignalBindingsFor(Signal|int|string $signal): int` — Counts bindings for signal
- `getSignalBindings(): array<string,Binding>` — Gets all signal bindings
- `getSignalBindingsFor(Signal|int|string $signal): array<string,Binding>` — Gets bindings for signal

**Timer Binding Operations:**
- `bindTimer(string $id, float $duration, callable $callback): static` — Binds persistent timer
- `bindFrozenTimer(string $id, float $duration, callable $callback): static` — Binds frozen persistent timer
- `bindTimerOnce(string $id, float $duration, callable $callback): static` — Binds one-time timer
- `bindFrozenTimerOnce(string $id, float $duration, callable $callback): static` — Binds frozen one-time timer
- `freezeTimer(string|TimerBinding $id): static` — Freezes timer binding
- `freezeAllTimers(): static` — Freezes all timer bindings
- `unfreezeTimer(string|TimerBinding $id): static` — Unfreezes timer binding
- `unfreezeAllTimers(): static` — Unfreezes all timer bindings
- `removeTimer(string|TimerBinding $id): static` — Removes timer binding
- `removeAllTimers(): static` — Removes all timer bindings
- `getTimerBinding(string|TimerBinding $id): ?TimerBinding` — Gets timer binding by ID
- `countTimerBindings(): int` — Counts timer bindings
- `getTimerBindings(): array<string,Binding>` — Gets all timer bindings

**Binding Operations:**
- `freeze(): static` — Freezes binding (via dispatcher)
- `unfreeze(): static` — Unfreezes binding (via dispatcher)
- `markFrozen(bool $frozen): static` — Sets frozen state directly
- `isFrozen(): bool` — Checks if binding is frozen
- `destroy(): static` — Removes binding from dispatcher
- `trigger(mixed $targetResource): static` — Triggers binding callback

**Signal Operations:**
- `Signal::create(Signal|string|int $signal): Signal` — Creates Signal object from identifier
- `Signal::normalizeSignalName(string $signal): string` — Normalizes signal name/number to name

### 5.3 Binding Lifecycle

Bindings have the following lifecycle:
1. Created via bind methods
2. Registered with dispatcher (unless frozen)
3. Triggered when event occurs (if not frozen)
4. Removed if one-time and triggered, or if explicitly removed
5. Destroyed when removed

### 5.4 Freeze/Unfreeze Semantics

Freezing a binding prevents it from triggering callbacks but keeps it registered. Unfreezing re-enables triggering. Frozen bindings can be unfrozen at any time. Freezing is useful for temporarily disabling event handling without removing bindings.

### 5.5 Persistent vs One-Time Bindings

Persistent bindings remain active after triggering and continue to handle events. One-time bindings are automatically removed after the first trigger. One-time bindings are useful for handling events that should only occur once.

### 5.6 Timeout Handling

IO bindings can have timeouts. If timeout occurs before IO event, timeout handler is called instead of regular callback. If no timeout handler is provided, timeout is ignored. Timeouts are in seconds (float).

### 5.7 Cycle Handler

Cycle handler is called approximately once per second during event loop. It receives cycle count and dispatcher instance. Returning false stops the loop. Useful for periodic checks and cleanup.

### 5.8 Tick Handler

Tick handler is called frequently during event loop (every 0.01 seconds in Event dispatcher, every loop iteration in Select dispatcher). It receives dispatcher instance. Returning false stops the loop. Useful for frequent checks and coordination.

### 5.9 Event Dispatcher

Event dispatcher uses PHP's Event extension for high-performance event loops. It automatically registers events with EventBase and handles event callbacks. It supports all binding types with native Event extension support.

### 5.10 Select Dispatcher

Select dispatcher uses `socket_select()` and `stream_select()` for event loops. It polls for ready sockets/streams and triggers callbacks. It uses `pcntl_signal()` for signal handling if PCNTL extension is available. It implements timer handling via time tracking.

---

## 6. Error Handling

- Invalid signal identifiers throw `Exceptional::InvalidArgument` in Signal operations
- Invalid binding IDs throw `Exceptional::InvalidArgument` in binding operations
- Event registration failures throw `Exceptional::Runtime` in Event dispatcher
- Socket/stream select errors are caught and handled gracefully in Select dispatcher
- Binding callback exceptions stop the event loop and propagate
- Cycle/tick handler exceptions stop the event loop and propagate
- Invalid IO modes throw `Exceptional::InvalidArgument` in Event dispatcher
- Provider throws `Exceptional::Runtime` if dispatcher is changed while running
- Provider throws `Exceptional::Runtime` if dispatcher is accessed before being set

---

## 7. Configuration & Extensibility

- Custom dispatcher implementations can extend Dispatcher interface
- Custom binding types can extend Binding interface
- Cycle handler can be customized for application-specific logic
- Tick handler can be customized for application-specific logic
- Binding IDs can be customized for application-specific identification
- Timeout handling can be customized per binding
- Freeze/unfreeze behavior can be customized per binding
- Event dispatcher can be extended for additional Event extension features
- Select dispatcher can be extended for additional select() features

---

## 8. Interactions with Other Packages

### 8.1 Deliverance

Eventful uses Deliverance for:
- Stream channels for stream-based IO events
- Socket channels for socket-based IO events
- IO resource access via channel interfaces
- Channel identification and management

### 8.2 Coercion

Eventful uses Coercion for:
- Type conversion when normalizing signal identifiers
- Resource ID conversion for socket/stream identification
- Numeric conversion for signal numbers

### 8.3 Exceptional

Eventful uses Exceptional for:
- All exception handling
- Error reporting for invalid signals, bindings, and operations

### 8.4 Systemic

Systemic uses Eventful for:
- Process management event loops
- Signal handling in process management
- IO event coordination

---

## 9. Usage Examples

### 9.1 Basic Timer

```php
use DecodeLabs\Eventful\Factory;

$dispatcher = Factory::newDispatcher()
    ->bindTimer('timer1', 2, function($binding) {
        echo "Timer fired\n";
    })
    ->listen();
```

### 9.2 Stream Read

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Eventful\Factory;

$broker = Deliverance::newCliBroker();
$input = $broker->getFirstInputReceiver();

$dispatcher = Factory::newDispatcher()
    ->bindStreamRead($input, function($stream, $binding) use($broker) {
        $line = $broker->readLine();
        echo "You said: $line\n";
    })
    ->listen();
```

### 9.3 Frozen Binding

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Eventful\Factory;

$broker = Deliverance::newCliBroker();
$input = $broker->getFirstInputReceiver();

$dispatcher = Factory::newDispatcher()
    ->bindFrozenStreamRead($input, function($stream, $binding) use($broker) {
        $line = $broker->readLine();
        echo "You said: $line\n";
    })
    ->bindTimerOnce('unfreeze', 1, function($binding) use($input, $dispatcher) {
        $dispatcher->unfreezeStream($input);
    })
    ->listen();
```

### 9.4 Signal Handling

```php
use DecodeLabs\Eventful\Factory;
use DecodeLabs\Eventful\Signal;

$dispatcher = Factory::newDispatcher()
    ->bindSignal('shutdown', [Signal::SIGINT, Signal::SIGTERM], function($signal, $binding) {
        echo "Received signal: {$signal->name}\n";
        $binding->dispatcher->stop();
    })
    ->listen();
```

### 9.5 Cycle Handler

```php
use DecodeLabs\Eventful\Factory;

$dispatcher = Factory::newDispatcher()
    ->setCycleHandler(function($cycles, $dispatcher) {
        if ($cycles > 10) {
            return false; // Stop after 10 cycles
        }
        echo "Cycle: $cycles\n";
        return true;
    })
    ->listen();
```

### 9.6 Multiple Bindings

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Eventful\Factory;

$broker = Deliverance::newCliBroker();
$input = $broker->getFirstInputReceiver();

$dispatcher = Factory::newDispatcher()
    ->bindTimer('timer1', 2, function() use($broker) {
        $broker->writeLine('Timer 1');
    })
    ->bindFrozenStreamRead($input, function($stream, $binding) use($broker) {
        $broker->writeLine('You said: ' . $broker->readLine());
    })
    ->bindTimerOnce('timer2', 1, function($binding) use($input, $dispatcher) {
        $dispatcher->unfreezeStream($input);
    })
    ->listen();
```

### 9.7 Socket Binding

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Eventful\Factory;

$socket = Deliverance::newSocket(/* ... */);

$dispatcher = Factory::newDispatcher()
    ->bindSocketRead($socket, function($socket, $binding) {
        $data = $socket->read();
        // Process data
    })
    ->bindSocketWrite($socket, function($socket, $binding) {
        // Write data when socket is writable
    })
    ->listen();
```

### 9.8 Timeout Handling

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Eventful\Factory;

$stream = /* ... */;

$dispatcher = Factory::newDispatcher()
    ->bindStreamRead(
        $stream,
        function($stream, $binding) {
            // Handle read
        },
        5.0, // 5 second timeout
        function($stream, $binding) {
            echo "Read timeout\n";
        }
    )
    ->listen();
```

### 9.9 One-Time Timer

```php
use DecodeLabs\Eventful\Factory;

$dispatcher = Factory::newDispatcher()
    ->bindTimerOnce('delayed', 1, function($binding) {
        echo "This runs once after 1 second\n";
    })
    ->listen();
```

### 9.10 Provider Pattern

```php
use DecodeLabs\Eventful\Factory;
use DecodeLabs\Eventful\Provider;
use DecodeLabs\Eventful\ProviderTrait;

class MyService implements Provider
{
    use ProviderTrait;

    public function __construct()
    {
        $this->eventDispatcher = Factory::newDispatcher();
    }

    public function start()
    {
        $this->eventDispatcher->bindTimer('heartbeat', 1, function() {
            echo "Heartbeat\n";
        });
        $this->eventDispatcher->listen();
    }
}
```

---

## 10. Implementation Notes (for Contributors)

### 10.1 Dispatcher Selection

Factory automatically selects dispatcher:
1. Checks if `ext-event` is loaded
2. If yes, uses Event dispatcher
3. If no, uses Select dispatcher

### 10.2 Event Dispatcher

Event dispatcher:
- Uses EventBase for event loop management
- Registers Event objects for each binding
- Handles event callbacks via Event extension
- Manages event lifecycle (add, free)
- Supports all binding types natively

### 10.3 Select Dispatcher

Select dispatcher:
- Uses socket_select() for socket monitoring
- Uses stream_select() for stream monitoring
- Uses pcntl_signal() for signal handling (if available)
- Implements timer handling via microtime() tracking
- Regenerates resource maps when bindings change
- Polls with 10ms timeout for responsiveness

### 10.4 Binding ID Generation

Binding IDs are generated as:
- Socket: `{ioMode}:{socketId}` (e.g., `r:socket123`)
- Stream: `{ioMode}:{streamId}` (e.g., `r:456`)
- Signal: User-provided string ID
- Timer: User-provided string ID

### 10.5 Resource Map Generation

Select dispatcher generates resource maps:
- Socket map: Maps resource IDs to sockets and bindings
- Stream map: Maps resource IDs to streams and bindings
- Signal map: Maps signal numbers to bindings
- Maps are regenerated when bindings change

### 10.6 Signal Normalization

Signal normalization:
- Checks if signal name is defined constant
- Falls back to `kill -l` if PCNTL not available
- Supports numeric signal identifiers
- Validates signal names against known signals

### 10.7 Timer Implementation

Timer implementation:
- Event dispatcher: Uses Event::TIMEOUT with duration
- Select dispatcher: Tracks last trigger time, checks elapsed time each loop
- Persistent timers: Reset after trigger
- One-time timers: Removed after trigger

### 10.8 Freeze/Unfreeze Implementation

Freeze/unfreeze:
- Event dispatcher: Unregisters/registers Event objects
- Select dispatcher: Sets frozen flag, skips in loop
- Frozen bindings: Do not trigger callbacks
- Unfrozen bindings: Resume normal operation

### 10.9 Cycle Handler Implementation

Cycle handler:
- Event dispatcher: Uses Event::TIMEOUT with 1 second duration, persistent
- Select dispatcher: Checks elapsed time since last cycle, calls if >1 second
- Returns false to stop loop
- Receives cycle count and dispatcher

### 10.10 Tick Handler Implementation

Tick handler:
- Event dispatcher: Uses Event::TIMEOUT with 0.01 second duration, persistent
- Select dispatcher: Called every loop iteration
- Returns false to stop loop
- Receives dispatcher instance

---

## 11. Testing & Quality

- **Code Quality Score:** 4/5
- **README Quality Score:** 3/5
- **Documentation Score:** 0/5 (this spec)
- **Test Coverage Score:** 0/5

See `composer.json` for supported PHP versions.

---

## 12. Roadmap & Future Ideas

- Add more dispatcher implementations (e.g., libev, libuv)
- Improve timeout handling in Select dispatcher
- Add binding priority support
- Add binding groups for batch operations
- Improve error handling and recovery
- Add binding statistics and monitoring
- Consider adding async/await support
- Add more signal handling options
- Improve timer precision
- Add binding lifecycle events
- Consider adding event bus integration
- Add more documentation and examples
- Improve test coverage

---

## 13. References

- [Coercion Package](https://github.com/decodelabs/coercion) — Type conversion
- [Deliverance Package](https://github.com/decodelabs/deliverance) — IO channels
- [Exceptional Package](https://github.com/decodelabs/exceptional) — Exception handling
- [Systemic Package](https://github.com/decodelabs/systemic) — Process management (uses Eventful)
- [PHP Event Extension](https://www.php.net/manual/en/book.event.php) — Event extension documentation
- [PHP PCNTL Extension](https://www.php.net/manual/en/book.pcntl.php) — PCNTL extension documentation
- [Chorus Package Index](../../../chorus/config/packages.json) — Ecosystem metadata

