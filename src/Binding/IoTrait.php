<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use DecodeLabs\Eventful\Binding;

trait IoTrait
{
    /**
     * @var string
     */
    public $ioMode = 'r';

    /**
     * @var float|null
     */
    public $timeout;

    /**
     * @var callable|null
     */
    public $timeoutHandler;

    /**
     * Get whether binding is read or write
     */
    public function getIoMode(): string
    {
        return $this->ioMode;
    }


    /**
     * Get timeout duration
     */
    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    /**
     * Get timeout callback handler
     */
    public function getTimeoutHandler(): ?callable
    {
        return $this->timeoutHandler;
    }
}
