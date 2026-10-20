<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Service;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Fragment\FragmentUriGeneratorInterface;
use Throwable;

/**
 * Builds {@see FragmentFailureContext} from an exception and the request stack.
 */
final readonly class FragmentFailureContextFactory
{
    /**
     * Uses the request stack (parent page data) and the optional fragment URI generator.
     */
    public function __construct(
        private RequestStack $requestStack,
        private ?FragmentUriGeneratorInterface $fragmentUriGenerator = null,
    ) {
    }

    /**
     * @param ControllerReference|string|null $fragment The URI / controller passed to the fragment handler. When given,
     *                                                  the fragment URI and controller come from it and the current request
     *                                                  is treated as the parent page (the inline renderer has already
     *                                                  popped the sub-request when the failure surfaces).
     */
    public function fromException(Throwable $exception, string|ControllerReference|null $fragment = null): FragmentFailureContext
    {
        if ($fragment !== null) {
            return $this->fromFragment($exception, $fragment);
        }

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

    private function fromFragment(Throwable $exception, string|ControllerReference $fragment): FragmentFailureContext
    {
        $parentRequest = $this->requestStack->getCurrentRequest();

        return new FragmentFailureContext(
            exception: $exception,
            statusCode: $this->resolveStatusCode($exception),
            fragmentUri: $fragment instanceof ControllerReference ? $this->generateFragmentUri($fragment, $parentRequest) : $fragment,
            parentRoute: $parentRequest instanceof Request ? $parentRequest->attributes->getString('_route') ?: null : null,
            parentUri: $parentRequest instanceof Request ? $parentRequest->getRequestUri() : null,
            controller: $fragment instanceof ControllerReference ? $fragment->controller : null,
        );
    }

    private function generateFragmentUri(ControllerReference $controller, ?Request $parentRequest): ?string
    {
        if (!$this->fragmentUriGenerator instanceof FragmentUriGeneratorInterface) {
            return null;
        }

        try {
            // Unsigned: the URI is only used for diagnostics (fallback template, error reporting).
            return $this->fragmentUriGenerator->generate($controller, $parentRequest, false, false, false);
        } catch (Throwable) {
            return null;
        }
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
