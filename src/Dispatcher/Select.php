<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Dispatcher;

use DecodeLabs\Coercion;
use DecodeLabs\Eventful\Binding;
use DecodeLabs\Eventful\Binding\Signal as SignalBinding;
use DecodeLabs\Eventful\Binding\Socket as SocketBinding;
use DecodeLabs\Eventful\Binding\Stream as StreamBinding;
use DecodeLabs\Eventful\Binding\Timer as TimerBinding;
use DecodeLabs\Eventful\Dispatcher;
use DecodeLabs\Eventful\DispatcherTrait;
use DecodeLabs\Exceptional;
use Socket;
use Throwable;

class Select implements Dispatcher
{
    use DispatcherTrait;

    protected const Read = 'r';
    protected const Write = 'w';

    protected const Resource = 0;
    protected const Handler = 1;

    protected bool $breakLoop = false;
    protected bool $generateMaps = true;


    /**
     * @var array<int, array<string, array<int, resource|Socket|array<string, Binding>>>>|null
     */
    protected ?array $socketMap = [];

    /**
     * @var array<int, array<string, array<int, resource|array<string, Binding>>>>|null
     */
    protected ?array $streamMap = [];

    /**
     * @var array<int, array<string, Binding>>|null
     */
    protected ?array $signalMap = [];

    /**
     * @var array<int,int|callable>
     */
    protected ?array $originalSignalHandlers = [];

    private bool $hasPcntl = false;

    /**
     * Check pcntl loaded
     */
    public function __construct()
    {
        $this->hasPcntl = extension_loaded('pcntl');
    }


    /**
     * Listen for events in loop
     */
    public function listen(): static
    {
        $this->breakLoop = false;
        $this->listening = true;

        $baseTime = microtime(true);
        $times = [];
        $lastCycle = $baseTime;
        $this->generateMaps = false;
        $this->generateMaps();

        $this->startSignalHandlers();
        $this->breakLoop = false;

        while (!$this->breakLoop) {
            $socketCount = count($this->sockets);
            $streamCount = count($this->streams);
            $signalCount = count($this->signals);
            $timerCount = count($this->timers);

            if ($this->generateMaps) {
                $this->generateMaps();
            }

            $hasHandler = false;


            // Timers
            if (!empty($this->timers)) {
                $hasHandler = true;
                $time = microtime(true);

                foreach ($this->timers as $id => $binding) {
                    if ($binding->frozen) {
                        continue;
                    }

                    $dTime = $times[$id] ?? $baseTime;
                    $diff = $time - $dTime;

                    if ($diff > $binding->duration) {
                        $times[$id] = $time;
                        $binding->trigger(null);
                    }
                }
            }



            // Signals
            if (
                !empty($this->signals) &&
                $this->hasPcntl
            ) {
                $hasHandler = true;
                pcntl_signal_dispatch();
            }

            // Sockets
            if (!empty($this->socketMap)) {
                $hasHandler = true;
                $e = null;

                /** @var array<int,resource|Socket> $read */
                $read = $this->socketMap[self::Resource][self::Read];
                /** @var array<int,resource|Socket> $write */
                $write = $this->socketMap[self::Resource][self::Write];

                try {
                    $res = socket_select($read, $write, $e, 0, 10000);
                } catch (Throwable $e) {
                    $res = false;
                }

                if ($res === false) {
                    // TODO: deal with error
                } elseif ($res > 0) {
                    foreach ($read as $resourceId => $socket) {
                        foreach (Coercion::asArray(
                            $this->socketMap[self::Handler][self::Read][$resourceId]
                        ) as $id => $binding) {
                            /** @var Binding $binding */
                            $binding->trigger($socket);
                        }
                    }

                    foreach ($write as $resourceId => $socket) {
                        foreach (Coercion::asArray(
                            $this->socketMap[self::Handler][self::Write][$resourceId]
                        ) as $id => $binding) {
                            /** @var Binding $binding */
                            $binding->trigger($socket);
                        }
                    }
                }

                // TODO: add timeout handler
            }

            // Streams
            if (!empty($this->streamMap)) {
                $hasHandler = true;
                $e = null;

                /** @var array<int, resource> $read */
                $read = $this->streamMap[self::Resource][self::Read];
                /** @var array<int, resource> $write */
                $write = $this->streamMap[self::Resource][self::Write];

                try {
                    $res = stream_select($read, $write, $e, 0, 10000);
                } catch (Throwable $e) {
                    $res = false;
                }


                if ($res === false) {
                    // TODO: deal with error
                } elseif ($res > 0) {
                    foreach ($read as $resourceId => $stream) {
                        foreach (Coercion::asArray(
                            $this->streamMap[self::Handler][self::Read][$resourceId]
                        ) as $id => $binding) {
                            /** @var Binding $binding */
                            $binding->trigger($stream);
                        }
                    }

                    foreach ($write as $resourceId => $stream) {
                        foreach (Coercion::asArray(
                            $this->streamMap[self::Handler][self::Write][$resourceId]
                        ) as $id => $binding) {
                            /** @var Binding $binding */
                            $binding->trigger($stream);
                        }
                    }
                }

                // TODO: add timeout handler
            }


            // Tick
            if (
                !$this->breakLoop &&
                $this->tickHandler
            ) {
                if (false === ($this->tickHandler)($this)) {
                    $this->breakLoop = true;
                }
            }

            // Cycle
            if (
                !$this->breakLoop &&
                $this->cycleHandler
            ) {
                $time = microtime(true);

                if ($time - $lastCycle > 1) {
                    $lastCycle = $time;

                    if (false === ($this->cycleHandler)(++$this->cycles, $this)) {
                        $this->breakLoop = true;
                    }
                }
            }


            if (!$hasHandler) {
                $this->breakLoop = true;
            } elseif (
                $socketCount !== count($this->sockets) ||
                $streamCount !== count($this->streams) ||
                $signalCount !== count($this->signals) ||
                $timerCount !== count($this->timers)
            ) {
                $this->generateMaps = true;
            }

            usleep(30000);
        }

        $this->breakLoop = false;
        $this->listening = false;

        $this->stopSignalHandlers();

        return $this;
    }

    /**
     * Flag to regenerate maps on next loop
     */
    public function regenerateMaps(): static
    {
        $this->generateMaps = true;
        return $this;
    }

    /**
     * Generate resource maps for select()
     */
    private function generateMaps(): void
    {
        $this->socketMap = $this->streamMap = [
            self::Resource => [
                self::Read => [],
                self::Write => []
            ],
            self::Handler => [
                self::Read => [],
                self::Write => []
            ]
        ];

        $socketCount = $streamCount = 0;



        // Sockets
        foreach ($this->sockets as $id => $binding) {
            /** @var resource|Socket $socket */
            $socket = $binding->ioResource;
            $resourceId = $this->identifySocket($socket);

            if ($binding->isStreamBased()) {
                /** @var resource $socket */
                $this->streamMap[self::Resource][$binding->ioMode][$resourceId] = $socket;
                $this->streamMap[self::Handler][$binding->ioMode][$resourceId][$id] = $binding;
                $streamCount++;
            } else {
                $this->socketMap[self::Resource][$binding->ioMode][$resourceId] = $socket;
                $this->socketMap[self::Handler][$binding->ioMode][$resourceId][$id] = $binding;
                $socketCount++;
            }
        }


        // Streams
        foreach ($this->streams as $id => $binding) {
            /** @var resource $stream */
            $stream = $binding->ioResource;
            $resourceId = (int)$stream;

            $this->streamMap[self::Resource][$binding->ioMode][$resourceId] = $stream;
            $this->streamMap[self::Handler][$binding->ioMode][$resourceId][$id] = $binding;
            $streamCount++;
        }


        // Signals
        $this->signalMap = [];

        foreach ($this->signals as $id => $binding) {
            foreach (array_keys($binding->signals) as $number) {
                $this->signalMap[$number][$id] = $binding;
            }
        }

        // Cleanup
        if (!$socketCount) {
            $this->socketMap = null;
        }

        if (!$streamCount) {
            $this->streamMap = null;
        }

        $this->generateMaps = false;
    }


    /**
     * Convert socket resource to ID string
     */
    protected function identifySocket(
        mixed $socket
    ): int {
        if (is_resource($socket)) {
            return (int)$socket;
        }

        if ($socket instanceof Socket) {
            return spl_object_id($socket);
        }

        throw Exceptional::InvalidArgument(
            message: 'Unable to identify socket'
        );
    }


    /**
     * Stop listening and return control
     */
    public function stop(): static
    {
        if ($this->listening) {
            $this->breakLoop = true;
        }

        return $this;
    }


    /**
     * Freeze binding
     */
    public function freezeBinding(
        Binding $binding
    ): static {
        $binding->markFrozen(true);
        return $this;
    }

    /**
     * Unfreeze binding
     */
    public function unfreezeBinding(
        Binding $binding
    ): static {
        $binding->markFrozen(false);
        return $this;
    }



    /**
     * Add new socket to maps
     */
    protected function registerSocketBinding(
        SocketBinding $binding
    ): void {
        $this->regenerateMaps();
    }

    /**
     * Remove socket from maps
     */
    protected function unregisterSocketBinding(
        SocketBinding $binding
    ): void {
        $this->regenerateMaps();
    }



    /**
     * Add new stream to maps
     */
    protected function registerStreamBinding(
        StreamBinding $binding
    ): void {
        $this->regenerateMaps();
    }

    /**
     * Remove stream from maps
     */
    protected function unregisterStreamBinding(
        StreamBinding $binding
    ): void {
        $this->regenerateMaps();
    }



    /**
     * Start listening for signals
     */
    protected function startSignalHandlers(): void
    {
        if (!$this->hasPcntl) {
            return;
        }

        foreach ($this->signalMap ?? [] as $number => $set) {
            // @phpstan-ignore-next-line
            $this->originalSignalHandlers[$number] = pcntl_signal_get_handler($number);

            pcntl_signal($number, function ($number) use ($set) {
                foreach ($set as $binding) {
                    /** @var Binding $binding */
                    $binding->trigger($number);
                }
            });
        }
    }

    /**
     * Stop listening for signals
     */
    protected function stopSignalHandlers(): void
    {
        if (!$this->hasPcntl) {
            return;
        }

        foreach (array_keys($this->signalMap ?? []) as $number) {
            pcntl_signal((int)$number, $this->originalSignalHandlers[$number] ?? \SIG_IGN);
        }

        $this->originalSignalHandlers = [];
    }


    /**
     * Add new signal to maps
     */
    protected function registerSignalBinding(
        SignalBinding $binding
    ): void {
        $this->regenerateMaps();
    }

    /**
     * Remove signal from maps
     */
    protected function unregisterSignalBinding(
        SignalBinding $binding
    ): void {
        $this->regenerateMaps();
    }




    /**
     * Noop
     */
    protected function registerTimerBinding(
        TimerBinding $binding
    ): void {
    }

    /**
     * Noop
     */
    protected function unregisterTimerBinding(
        TimerBinding $binding
    ): void {
    }
}
