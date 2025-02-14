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
    protected(set) string $ioMode = 'r';
    protected(set) ?float $timeout = null;
    protected(set) ?Closure $timeoutHandler = null;
}
