<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\Fixtures;

use LogicException;
use Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\ResilientFragmentKernelTest;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Parent pages and fragment controllers used by {@see ResilientFragmentKernelTest}.
 */
final readonly class FragmentTestController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function page(string $template): Response
    {
        return new Response($this->twig->render($template));
    }

    public function boom(): Response
    {
        throw new LogicException('Fragment controller exploded');
    }

    public function forbidden(): Response
    {
        return new Response('nope', Response::HTTP_FORBIDDEN);
    }

    public function ok(): Response
    {
        return new Response('ALT-OK');
    }
}
