<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Service;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Twig\Environment;

/**
 * Renders Twig fallback content for a suppressed fragment failure.
 */
final readonly class FragmentFailureRenderer
{
    /**
     * Uses the configured fallback template (null or empty: render nothing).
     */
    public function __construct(
        private Environment $twig,
        private ?string $template,
    ) {
    }

    /**
     * Renders the fallback template for the given failure context (empty string when no template is configured).
     */
    public function render(FragmentFailureContext $context): string
    {
        if ($this->template === null || $this->template === '') {
            return '';
        }

        return $this->twig->render($this->template, [
            'status_code'  => $context->statusCode,
            'fragment_uri' => $context->fragmentUri,
            'route'        => $context->route,
            'parent_route' => $context->parentRoute,
            'parent_uri'   => $context->parentUri,
            'controller'   => $context->controller,
            'exception'    => $context->exception,
        ]);
    }
}
