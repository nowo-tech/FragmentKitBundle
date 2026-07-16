<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\Service;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Nowo\FragmentKitBundle\Service\FragmentFailureRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Twig\Environment;

#[CoversClass(FragmentFailureRenderer::class)]
final class FragmentFailureRendererTest extends TestCase
{
    public function testRenderReturnsEmptyStringWhenTemplateIsNull(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->never())->method('render');

        $renderer = new FragmentFailureRenderer($twig, null);
        $context  = new FragmentFailureContext(new RuntimeException('x'), 500);

        self::assertSame('', $renderer->render($context));
    }

    public function testRenderDelegatesToTwigWithContextVariables(): void
    {
        $exception = new RuntimeException('Error when rendering');
        $context   = new FragmentFailureContext(
            exception: $exception,
            statusCode: 403,
            fragmentUri: '/frag',
            route: 'child',
            parentRoute: 'parent',
            parentUri: '/',
            controller: 'App\\C::m',
        );

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with('fragment/unavailable.html.twig', [
                'status_code'  => 403,
                'fragment_uri' => '/frag',
                'route'        => 'child',
                'parent_route' => 'parent',
                'parent_uri'   => '/',
                'controller'   => 'App\\C::m',
                'exception'    => $exception,
            ])
            ->willReturn('<div>fallback</div>');

        $renderer = new FragmentFailureRenderer($twig, 'fragment/unavailable.html.twig');

        self::assertSame('<div>fallback</div>', $renderer->render($context));
    }
}
