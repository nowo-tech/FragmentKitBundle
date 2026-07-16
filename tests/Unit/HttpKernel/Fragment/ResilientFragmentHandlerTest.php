<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\HttpKernel\Fragment;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\HttpKernel\Fragment\ResilientFragmentHandler;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory;
use Nowo\FragmentKitBundle\Service\FragmentFailureRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

#[CoversClass(ResilientFragmentHandler::class)]
final class ResilientFragmentHandlerTest extends TestCase
{
    public function testRenderReturnsFallbackHtmlAndReportsWhenIgnoreErrorsIsTrue(): void
    {
        $exception = new \RuntimeException('Error when rendering "https://example.test/fragment" (Status code is 403).');
        $inner = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $context = new FragmentFailureContext(
            exception: $exception,
            statusCode: 403,
            fragmentUri: 'https://example.test/fragment',
            route: 'child_route',
            parentRoute: 'parent_route',
            parentUri: '/parent',
            controller: 'App\\Controller\\Foo::bar',
        );

        $contextFactory = $this->createMock(FragmentFailureContextFactory::class);
        $contextFactory->expects($this->once())->method('fromException')->with($exception)->willReturn($context);

        $failureRenderer = $this->createMock(FragmentFailureRenderer::class);
        $failureRenderer->expects($this->once())->method('render')->with($context)->willReturn('<div>fallback</div>');

        $reporter = $this->createMock(FragmentFailureReporterInterface::class);
        $reporter->expects($this->once())->method('report')->with($context);

        $handler = new ResilientFragmentHandler(
            $inner,
            new RequestStack(),
            $contextFactory,
            $failureRenderer,
            $reporter,
        );

        $result = $handler->render(
            new ControllerReference('App\\Controller\\Foo::bar'),
            'inline',
            ['ignore_errors' => true],
        );

        $this->assertSame('<div>fallback</div>', $result);
    }

    public function testRenderRethrowsWhenIgnoreErrorsIsFalse(): void
    {
        $exception = new \RuntimeException('Error when rendering "https://example.test/fragment" (Status code is 403).');
        $inner = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $handler = new ResilientFragmentHandler(
            $inner,
            new RequestStack(),
            $this->createMock(FragmentFailureContextFactory::class),
            $this->createMock(FragmentFailureRenderer::class),
            $this->createMock(FragmentFailureReporterInterface::class),
        );

        $this->expectExceptionObject($exception);
        $handler->render(new ControllerReference('App\\Controller\\Foo::bar'), 'inline', ['ignore_errors' => false]);
    }
}
