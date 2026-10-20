<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\HttpKernel\Fragment;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory;
use Nowo\FragmentKitBundle\Service\FragmentFailureRenderer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Throwable;

/**
 * Decorates fragment.handler so Twig {ignore_errors: true} also tolerates failing sub-requests
 * (controller exceptions and HTTP error responses), renders a fallback Twig template, and
 * optionally reports to Sentry.
 *
 * When errors are ignored, the inline renderer is called with `ignore_errors: false` so it no
 * longer swallows controller exceptions into an empty response: its `alt` fallback is still tried
 * first, and any remaining failure is handled here. Like Symfony, `ignore_errors` defaults to
 * `!kernel.debug` when the option is not set.
 *
 * Worker-safe with kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false): collaborators are
 * readonly; rendering delegates to the inner handler; no per-request state is stored here.
 */
final class ResilientFragmentHandler extends FragmentHandler
{
    /**
     * Wraps the core fragment handler so a failing sub-request renders a fallback instead of breaking the page.
     */
    public function __construct(
        private readonly FragmentHandler $inner,
        RequestStack $requestStack,
        private readonly FragmentFailureContextFactory $contextFactory,
        private readonly FragmentFailureRenderer $failureRenderer,
        private readonly FragmentFailureReporterInterface $failureReporter,
        private readonly bool $debug = false,
    ) {
        parent::__construct($requestStack, [], $debug);
    }

    /**
     * Adds a fragment renderer to the decorated core handler.
     */
    public function addRenderer(FragmentRendererInterface $renderer): void
    {
        $this->inner->addRenderer($renderer);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function render(string|ControllerReference $uri, string $renderer = 'inline', array $options = []): ?string
    {
        $ignoreErrors = (bool) ($options['ignore_errors'] ?? !$this->debug);

        // Surrogate renderers (esi, ssi) turn ignore_errors into markup (onerror="continue"): keep it.
        $options['ignore_errors'] = $renderer === 'inline' ? false : $ignoreErrors;

        try {
            return $this->inner->render($uri, $renderer, $options);
        } catch (Throwable $exception) {
            if (!$ignoreErrors) {
                throw $exception;
            }

            $context = $this->contextFactory->fromException($exception, $uri);
            $this->failureReporter->report($context);

            return $this->failureRenderer->render($context);
        }
    }
}
