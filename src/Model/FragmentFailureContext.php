<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Model;

use Throwable;

/**
 * Immutable context describing a suppressed fragment rendering failure.
 */
final readonly class FragmentFailureContext
{
    public function __construct(
        public Throwable $exception,
        public int $statusCode,
        public ?string $fragmentUri = null,
        public ?string $route = null,
        public ?string $parentRoute = null,
        public ?string $parentUri = null,
        public ?string $controller = null,
    ) {
    }
}
