<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Model;

class FragmentFailureContext
{
    public function __construct(
        public readonly \Throwable $exception,
        public readonly int $statusCode,
        public readonly ?string $fragmentUri,
        public readonly ?string $route,
        public readonly ?string $parentRoute,
        public readonly ?string $parentUri,
        public readonly ?string $controller,
    ) {
    }
}
