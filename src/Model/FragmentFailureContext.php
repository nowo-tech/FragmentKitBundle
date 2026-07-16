<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Model;

use Throwable;

/**
 * Immutable context describing a suppressed fragment rendering failure.
 */
class FragmentFailureContext
{
    public function __construct(
        public readonly Throwable $exception,
        public readonly int $statusCode,
        public readonly ?string $fragmentUri = null,
        public readonly ?string $route = null,
        public readonly ?string $parentRoute = null,
        public readonly ?string $parentUri = null,
        public readonly ?string $controller = null,
    ) {
    }
}
