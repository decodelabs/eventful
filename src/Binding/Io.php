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
    public function getIoResource(): mixed;
    public function getTimeout(): ?float;
    public function getTimeoutHandler(): ?callable;

    /**
     * @return $this
     */
    public function triggerTimeout(
        mixed $targetResource
    ): static;
}
