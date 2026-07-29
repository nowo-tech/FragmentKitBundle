<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\HttpKernel\Fragment;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory;
use Nowo\FragmentKitBundle\Service\FragmentFailureRenderer;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Throwable;

/**
 * Decorates fragment.handler so Twig {ignore_errors: true} also tolerates HTTP error
 * responses from sub-requests, renders a fallback Twig template, and optionally reports to Sentry.
 */
final class ResilientFragmentHandler extends FragmentHandler
{
    public function __construct(
        private readonly FragmentHandler $inner,
        RequestStack $requestStack,
        private readonly FragmentFailureContextFactory $contextFactory,
        private readonly FragmentFailureRenderer $failureRenderer,
        private readonly FragmentFailureReporterInterface $failureReporter,
    ) {
        parent::__construct($requestStack);
    }

    public function addRenderer(FragmentRendererInterface $renderer): void
    {
        $this->inner->addRenderer($renderer);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function render(string|ControllerReference $uri, string $renderer = 'inline', array $options = []): ?string
    {
        try {
            return $this->inner->render($uri, $renderer, $options);
        } catch (Throwable $exception) {
            if (!$exception instanceof RuntimeException || !$this->shouldIgnoreErrors($options, $exception)) {
                throw $exception;
            }

            $context = $this->contextFactory->fromException($exception);
            $this->failureReporter->report($context);

            return $this->failureRenderer->render($context);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function shouldIgnoreErrors(array $options, RuntimeException $exception): bool
    {
        if (!($options['ignore_errors'] ?? false)) {
            return false;
        }

        return str_starts_with($exception->getMessage(), 'Error when rendering');
    }
}
