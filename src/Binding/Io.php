<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use DecodeLabs\Eventful\Binding;

interface Io extends Binding
{
    public function getIoMode(): string;

    /**
     * @return mixed
     */
    public function getIoResource();

    public function getTimeout(): ?float;
    public function getTimeoutHandler(): ?callable;

    /**
     * @param mixed $targetResource
     */
    public function triggerTimeout($targetResource): Io;
}
