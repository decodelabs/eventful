<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use Closure;
use DecodeLabs\Eventful\Binding;

interface Io extends Binding
{
    public string $ioMode { get; }
    public ?float $timeout { get; }
    public ?Closure $timeoutHandler { get; }
    public mixed $ioResource { get; }

    /**
     * @return $this
     */
    public function triggerTimeout(
        mixed $targetResource
    ): static;
}
