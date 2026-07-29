<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\HttpKernel\Fragment;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\HttpKernel\Fragment\ResilientFragmentHandler;
use Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory;
use Nowo\FragmentKitBundle\Service\FragmentFailureRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Twig\Environment;

#[CoversClass(ResilientFragmentHandler::class)]
final class ResilientFragmentHandlerTest extends TestCase
{
    public function testRenderReturnsFallbackHtmlAndReportsWhenIgnoreErrorsIsTrue(): void
    {
        $exception = new RuntimeException('Error when rendering "https://example.test/fragment" (Status code is 403).');
        $inner     = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with(
                'fragment/unavailable.html.twig',
                $this->callback(static fn (array $context): bool => $context['status_code'] === 403
                    && $context['fragment_uri'] === 'https://example.test/fragment'
                    && $context['exception'] === $exception),
            )
            ->willReturn('<div>fallback</div>');

        $reporter = $this->createMock(FragmentFailureReporterInterface::class);
        $reporter->expects($this->once())->method('report');

        $handler = new ResilientFragmentHandler(
            $inner,
            new RequestStack(),
            new FragmentFailureContextFactory(new RequestStack()),
            new FragmentFailureRenderer($twig, 'fragment/unavailable.html.twig'),
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
        $exception = new RuntimeException('Error when rendering "https://example.test/fragment" (Status code is 403).');
        $inner     = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $handler = new ResilientFragmentHandler(
            $inner,
            new RequestStack(),
            new FragmentFailureContextFactory(new RequestStack()),
            new FragmentFailureRenderer($this->createMock(Environment::class), null),
            $this->createMock(FragmentFailureReporterInterface::class),
        );

        $this->expectExceptionObject($exception);
        $handler->render(new ControllerReference('App\\Controller\\Foo::bar'), 'inline', ['ignore_errors' => false]);
    }

    public function testAddRendererDelegatesToInnerHandler(): void
    {
        $inner    = $this->createMock(FragmentHandler::class);
        $renderer = $this->createMock(FragmentRendererInterface::class);
        $inner->expects($this->once())->method('addRenderer')->with($renderer);

        $handler = new ResilientFragmentHandler(
            $inner,
            new RequestStack(),
            new FragmentFailureContextFactory(new RequestStack()),
            new FragmentFailureRenderer($this->createMock(Environment::class), null),
            $this->createMock(FragmentFailureReporterInterface::class),
        );

        $handler->addRenderer($renderer);
    }

    public function testRenderRethrowsNonMatchingRuntimeExceptionEvenWithIgnoreErrors(): void
    {
        $exception = new RuntimeException('Unrelated runtime failure');
        $inner     = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $handler = new ResilientFragmentHandler(
            $inner,
            new RequestStack(),
            new FragmentFailureContextFactory(new RequestStack()),
            new FragmentFailureRenderer($this->createMock(Environment::class), null),
            $this->createMock(FragmentFailureReporterInterface::class),
        );

        $this->expectExceptionObject($exception);
        $handler->render('/', 'inline', ['ignore_errors' => true]);
    }
}
