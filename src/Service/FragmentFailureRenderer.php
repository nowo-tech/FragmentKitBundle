<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Service;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Twig\Environment;

/**
 * Renders Twig fallback content for a suppressed fragment failure.
 */
class FragmentFailureRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ?string $template,
    ) {
    }

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
