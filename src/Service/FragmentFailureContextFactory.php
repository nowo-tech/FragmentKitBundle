<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Service;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Builds {@see FragmentFailureContext} from an exception and the request stack.
 */
class FragmentFailureContextFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function fromException(Throwable $exception): FragmentFailureContext
    {
        $statusCode    = $this->resolveStatusCode($exception);
        $fragmentUri   = $this->resolveFragmentUri($exception);
        $request       = $this->requestStack->getCurrentRequest();
        $parentRequest = $this->requestStack->getParentRequest();

        return new FragmentFailureContext(
            exception: $exception,
            statusCode: $statusCode,
            fragmentUri: $fragmentUri,
            route: $request instanceof Request ? $request->attributes->getString('_route') ?: null : null,
            parentRoute: $parentRequest instanceof Request ? $parentRequest->attributes->getString('_route') ?: null : null,
            parentUri: $parentRequest instanceof Request ? $parentRequest->getRequestUri() : null,
            controller: $request instanceof Request ? $request->attributes->getString('_controller') ?: null : null,
        );
    }

    private function resolveStatusCode(Throwable $exception): int
    {
        $current = $exception;

        while ($current instanceof Throwable) {
            if ($current instanceof HttpException) {
                return $current->getStatusCode();
            }

            $current = $current->getPrevious();
        }

        if (preg_match('/\(Status code is (\d+)\)/', $exception->getMessage(), $matches) === 1) {
            return (int) $matches[1];
        }

        return 500;
    }

    private function resolveFragmentUri(Throwable $exception): ?string
    {
        if (preg_match('/Error when rendering "(.+)" \(Status code is \d+\)/', $exception->getMessage(), $matches) === 1) {
            return $matches[1];
        }

        $request = $this->requestStack->getCurrentRequest();

        return $request instanceof Request ? $request->getRequestUri() : null;
    }
}
