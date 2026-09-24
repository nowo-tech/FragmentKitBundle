<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Null;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;

/**
 * No-op reporter used when Sentry is disabled or unavailable.
 *
 * Stateless — safe under FrankenPHP worker with kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false).
 */
final readonly class NullFragmentFailureReporter implements FragmentFailureReporterInterface
{
    public function report(FragmentFailureContext $context): void
    {
    }
}
