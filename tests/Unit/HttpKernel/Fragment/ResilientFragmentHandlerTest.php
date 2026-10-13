<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\HttpKernel\Fragment;

use LogicException;
use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\HttpKernel\Fragment\ResilientFragmentHandler;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory;
use Nowo\FragmentKitBundle\Service\FragmentFailureRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
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
        $exception = new RuntimeException('Error when rendering "https://example.test/parent" (Status code is 403).');
        $inner     = $this->createMock(FragmentHandler::class);
        $inner->expects($this->once())
            ->method('render')
            ->with('/fragment', 'inline', ['ignore_errors' => false])
            ->willThrowException($exception);

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with(
                'fragment/unavailable.html.twig',
                $this->callback(static fn (array $context): bool => $context['status_code'] === 403
                    && $context['fragment_uri'] === '/fragment'
                    && $context['exception'] === $exception),
            )
            ->willReturn('<div>fallback</div>');

        $reporter = $this->createMock(FragmentFailureReporterInterface::class);
        $reporter->expects($this->once())->method('report');

        $handler = $this->createHandler($inner, $reporter, new FragmentFailureRenderer($twig, 'fragment/unavailable.html.twig'));

        $this->assertSame('<div>fallback</div>', $handler->render('/fragment', 'inline', ['ignore_errors' => true]));
    }

    public function testControllerExceptionIsHandledWithFragmentContextWhenIgnoreErrorsIsTrue(): void
    {
        $exception = new LogicException('Widget exploded');
        $inner     = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $parent = Request::create('/page');
        $parent->attributes->set('_route', 'app.page');
        $stack = new RequestStack();
        $stack->push($parent);

        $reported = null;
        $reporter = $this->createMock(FragmentFailureReporterInterface::class);
        $reporter->expects($this->once())
            ->method('report')
            ->willReturnCallback(static function (FragmentFailureContext $context) use (&$reported): void {
                $reported = $context;
            });

        $handler = new ResilientFragmentHandler(
            $inner,
            $stack,
            new FragmentFailureContextFactory($stack),
            new FragmentFailureRenderer($this->createMock(Environment::class), null),
            $reporter,
        );

        $this->assertSame('', $handler->render(new ControllerReference('App\\Controller\\Widget::index'), 'inline', ['ignore_errors' => true]));
        $this->assertInstanceOf(FragmentFailureContext::class, $reported);
        $this->assertSame($exception, $reported->exception);
        $this->assertSame(500, $reported->statusCode);
        $this->assertSame('App\\Controller\\Widget::index', $reported->controller);
        $this->assertNull($reported->route);
        $this->assertSame('app.page', $reported->parentRoute);
        $this->assertSame('/page', $reported->parentUri);
    }

    public function testRenderRethrowsWhenIgnoreErrorsIsFalse(): void
    {
        $exception = new RuntimeException('Error when rendering "https://example.test/fragment" (Status code is 403).');
        $inner     = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $reporter = $this->createMock(FragmentFailureReporterInterface::class);
        $reporter->expects($this->never())->method('report');

        $this->expectExceptionObject($exception);
        $this->createHandler($inner, $reporter)
            ->render(new ControllerReference('App\\Controller\\Foo::bar'), 'inline', ['ignore_errors' => false]);
    }

    public function testIgnoreErrorsDefaultsToTrueWhenNotDebug(): void
    {
        $inner = $this->createMock(FragmentHandler::class);
        $inner->expects($this->once())
            ->method('render')
            ->with('/fragment', 'inline', ['ignore_errors' => false])
            ->willThrowException(new LogicException('boom'));

        $reporter = $this->createMock(FragmentFailureReporterInterface::class);
        $reporter->expects($this->once())->method('report');

        $this->assertSame('', $this->createHandler($inner, $reporter, debug: false)->render('/fragment'));
    }

    public function testIgnoreErrorsDefaultsToFalseInDebug(): void
    {
        $exception = new LogicException('boom');
        $inner     = $this->createMock(FragmentHandler::class);
        $inner->method('render')->willThrowException($exception);

        $reporter = $this->createMock(FragmentFailureReporterInterface::class);
        $reporter->expects($this->never())->method('report');

        $this->expectExceptionObject($exception);
        $this->createHandler($inner, $reporter, debug: true)->render('/fragment');
    }

    public function testSurrogateRenderersKeepIgnoreErrorsOption(): void
    {
        $inner = $this->createMock(FragmentHandler::class);
        $inner->expects($this->once())
            ->method('render')
            ->with('/fragment', 'esi', ['ignore_errors' => true, 'alt' => '/alt'])
            ->willReturn('<esi:include src="/fragment" alt="/alt" onerror="continue" />');

        $result = $this->createHandler($inner, $this->createMock(FragmentFailureReporterInterface::class))
            ->render('/fragment', 'esi', ['ignore_errors' => true, 'alt' => '/alt']);

        $this->assertSame('<esi:include src="/fragment" alt="/alt" onerror="continue" />', $result);
    }

    public function testAddRendererDelegatesToInnerHandler(): void
    {
        $inner    = $this->createMock(FragmentHandler::class);
        $renderer = $this->createMock(FragmentRendererInterface::class);
        $inner->expects($this->once())->method('addRenderer')->with($renderer);

        $this->createHandler($inner, $this->createMock(FragmentFailureReporterInterface::class))->addRenderer($renderer);
    }

    private function createHandler(
        FragmentHandler $inner,
        FragmentFailureReporterInterface $reporter,
        ?FragmentFailureRenderer $renderer = null,
        bool $debug = false,
    ): ResilientFragmentHandler {
        return new ResilientFragmentHandler(
            $inner,
            new RequestStack(),
            new FragmentFailureContextFactory(new RequestStack()),
            $renderer ?? new FragmentFailureRenderer($this->createMock(Environment::class), null),
            $reporter,
            $debug,
        );
    }
}
