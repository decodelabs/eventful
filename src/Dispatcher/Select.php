<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Dispatcher;

use DecodeLabs\Eventful\Binding;
use DecodeLabs\Eventful\Binding\Signal as SignalBinding;
use DecodeLabs\Eventful\Binding\Socket as SocketBinding;
use DecodeLabs\Eventful\Binding\Stream as StreamBinding;
use DecodeLabs\Eventful\Binding\Timer as TimerBinding;

use DecodeLabs\Eventful\Dispatcher;
use DecodeLabs\Eventful\DispatcherTrait;

use Throwable;

class Select implements Dispatcher
{
    use DispatcherTrait;

    public const SIGNAL = 0;
    public const SOCKET = 1;
    public const STREAM = 2;
    public const TIMER = 3;

    public const READ = 'r';
    public const WRITE = 'w';

    public const RESOURCE = 0;
    public const HANDLER = 1;

    /**
     * @var bool
     */
    protected $breakLoop = false;

    /**
     * @var bool
     */
    protected $generateMaps = true;


    /**
     * @var array<int, array>|null
     */
    protected $socketMap = [];

    /**
     * @var array<int, array>|null
     */
    protected $streamMap = [];

    /**
     * @var array<int, array>|null
     */
    protected $signalMap = [];


    /**
     * @var bool
     */
    private $hasPcntl = false;

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
    public function listen(): Dispatcher
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
            if (!empty($this->signals) && $this->hasPcntl) {
                $hasHandler = true;
                pcntl_signal_dispatch();
            }

            // Sockets
            if (!empty($this->socketMap)) {
                $hasHandler = true;
                $read = $this->socketMap[self::RESOURCE][self::READ];
                $write = $this->socketMap[self::RESOURCE][self::WRITE];
                $e = null;

                try {
                    $res = socket_select($read, $write, $e, 0, 10000);
                } catch (Throwable $e) {
                    $res = false;
                }

                if ($res === false) {
                    // TODO: deal with error
                } elseif ($res > 0) {
                    foreach ($read as $resource) {
                        foreach ($this->socketMap[self::HANDLER][self::READ][(int)$resource] as $id => $binding) {
                            $binding->trigger($resource);
                        }
                    }

                    foreach ($write as $resource) {
                        foreach ($this->socketMap[self::HANDLER][self::WRITE][(int)$resource] as $id => $binding) {
                            $binding->trigger($resource);
                        }
                    }
                }

                // TODO: add timeout handler
            }

            // Streams
            if (!empty($this->streamMap)) {
                $hasHandler = true;
                $read = $this->streamMap[self::RESOURCE][self::READ];
                $write = $this->streamMap[self::RESOURCE][self::WRITE];
                $e = null;

                try {
                    $res = stream_select($read, $write, $e, 0, 10000);
                } catch (Throwable $e) {
                    $res = false;
                }

                if ($res === false) {
                    // TODO: deal with error
                } elseif ($res > 0) {
                    foreach ($read as $resource) {
                        foreach ($this->streamMap[self::HANDLER][self::READ][(int)$resource] as $id => $binding) {
                            $binding->trigger($resource);
                        }
                    }

                    foreach ($write as $resource) {
                        foreach ($this->streamMap[self::HANDLER][self::WRITE][(int)$resource] as $id => $binding) {
                            $binding->trigger($resource);
                        }
                    }
                }

                // TODO: add timeout handler
            }


            // Cycle
            if ($this->cycleHandler) {
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
    public function regenerateMaps(): Dispatcher
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
            self::RESOURCE => [
                self::READ => [],
                self::WRITE => []
            ],
            self::HANDLER => [
                self::READ => [],
                self::WRITE => []
            ]
        ];

        $socketCount = $streamCount = 0;



        // Sockets
        foreach ($this->sockets as $id => $binding) {
            $resource = $binding->getIoResource();
            $resourceId = (int)$resource;

            if ($binding->isStreamBased()) {
                $this->streamMap[self::RESOURCE][$binding->ioMode][$resourceId] = $resource;
                $this->streamMap[self::HANDLER][$binding->ioMode][$resourceId][$id] = $binding;
                $streamCount++;
            } else {
                $this->socketMap[self::RESOURCE][$binding->ioMode][$resourceId] = $resource;
                $this->socketMap[self::HANDLER][$binding->ioMode][$resourceId][$id] = $binding;
                $socketCount++;
            }
        }


        // Streams
        foreach ($this->streams as $id => $binding) {
            $resource = $binding->getIoResource();
            $resourceId = (int)$resource;

            $this->streamMap[self::RESOURCE][$binding->ioMode][$resourceId] = $resource;
            $this->streamMap[self::HANDLER][$binding->ioMode][$resourceId][$id] = $binding;
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
     * Stop listening and return control
     */
    public function stop(): Dispatcher
    {
        if ($this->listening) {
            $this->breakLoop = true;
        }

        return $this;
    }


    /**
     * Freeze binding
     */
    public function freezeBinding(Binding $binding): Dispatcher
    {
        $binding->markFrozen(true);
        return $this;
    }

    /**
     * Unfreeze binding
     */
    public function unfreezeBinding(Binding $binding): Dispatcher
    {
        $binding->markFrozen(false);
        return $this;
    }



    /**
     * Add new socket to maps
     */
    protected function registerSocketBinding(SocketBinding $binding): void
    {
        $this->regenerateMaps();
    }

    /**
     * Remove socket from maps
     */
    protected function unregisterSocketBinding(SocketBinding $binding): void
    {
        $this->regenerateMaps();
    }



    /**
     * Add new stream to maps
     */
    protected function registerStreamBinding(StreamBinding $binding): void
    {
        $this->regenerateMaps();
    }

    /**
     * Remove stream from maps
     */
    protected function unregisterStreamBinding(StreamBinding $binding): void
    {
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
            pcntl_signal($number, function ($number) use ($set) {
                foreach ($set as $binding) {
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
        if ($this->hasPcntl) {
            return;
        }

        foreach (array_keys($this->signalMap ?? []) as $number) {
            pcntl_signal((int)$number, \SIG_IGN);
        }
    }


    /**
     * Add new signal to maps
     */
    protected function registerSignalBinding(SignalBinding $binding): void
    {
        $this->regenerateMaps();
    }

    /**
     * Remove signal from maps
     */
    protected function unregisterSignalBinding(SignalBinding $binding): void
    {
        $this->regenerateMaps();
    }




    /**
     * Noop
     */
    protected function registerTimerBinding(TimerBinding $binding): void
    {
    }

    /**
     * Noop
     */
    protected function unregisterTimerBinding(TimerBinding $binding): void
    {
    }
}
