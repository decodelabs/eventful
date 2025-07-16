<?php

/**
 * @package Eventful
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Eventful\Binding;

use Closure;

/**
 * @phpstan-require-implements Io
 */
trait IoTrait
{
    public protected(set) string $ioMode = 'r';
    public protected(set) ?float $timeout = null;
    public protected(set) ?Closure $timeoutHandler = null;
}
