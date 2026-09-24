<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Contract;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Reports a suppressed fragment failure.
 *
 * Implementations must be safe when the Symfony kernel is reused across requests
 * (FrankenPHP worker, `FRANKENPHP_RESET_KERNEL` unset or {@code 0}): stay stateless,
 * or implement {@see ResetInterface}, and do not buffer
 * {@see FragmentFailureContext} instances (they retain the exception trace).
 */
interface FragmentFailureReporterInterface
{
    public function report(FragmentFailureContext $context): void;
}
