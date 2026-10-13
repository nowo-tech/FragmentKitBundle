<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\Service;

use LogicException;
use Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Fragment\FragmentUriGeneratorInterface;

#[CoversClass(FragmentFailureContextFactory::class)]
final class FragmentFailureContextFactoryTest extends TestCase
{
    /**
     * Legacy call without the fragment argument, made while the sub-request is still current.
     * ResilientFragmentHandler passes the fragment (see the tests below), because by then the
     * inline renderer has already popped the sub-request.
     */
    public function testBuildsContextFromExceptionWhileSubRequestIsCurrent(): void
    {
        $parent = Request::create('/parent');
        $parent->attributes->set('_route', 'app.parent');

        $child = Request::create('/child');
        $child->attributes->set('_route', 'app.child');
        $child->attributes->set('_controller', 'App\\Controller\\Child::index');

        $stack = new RequestStack();
        $stack->push($parent);
        $stack->push($child);

        $exception = new RuntimeException(
            'Error when rendering "https://example.test/fragment" (Status code is 403).',
            0,
            new HttpException(403),
        );

        $context = (new FragmentFailureContextFactory($stack))->fromException($exception);

        $this->assertSame(403, $context->statusCode);
        $this->assertSame('https://example.test/fragment', $context->fragmentUri);
        $this->assertSame('app.child', $context->route);
        $this->assertSame('app.parent', $context->parentRoute);
        $this->assertSame('/parent', $context->parentUri);
        $this->assertSame('App\\Controller\\Child::index', $context->controller);
    }

    public function testDefaultsTo500WhenStatusCannotBeResolved(): void
    {
        $stack     = new RequestStack();
        $exception = new RuntimeException('Something else went wrong');

        $context = (new FragmentFailureContextFactory($stack))->fromException($exception);

        $this->assertSame(500, $context->statusCode);
        $this->assertNull($context->fragmentUri);
    }

    public function testUsesCurrentRequestUriWhenMessageHasNoFragmentUri(): void
    {
        $request = Request::create('/current-fragment');
        $stack   = new RequestStack();
        $stack->push($request);

        $exception = new RuntimeException('Error when rendering without status pattern');

        $context = (new FragmentFailureContextFactory($stack))->fromException($exception);

        $this->assertSame(500, $context->statusCode);
        $this->assertSame('/current-fragment', $context->fragmentUri);
    }

    public function testParsesStatusCodeFromMessageWithoutHttpException(): void
    {
        $stack     = new RequestStack();
        $exception = new RuntimeException(
            'Error when rendering "https://example.test/x" (Status code is 404).',
        );

        $context = (new FragmentFailureContextFactory($stack))->fromException($exception);

        $this->assertSame(404, $context->statusCode);
        $this->assertSame('https://example.test/x', $context->fragmentUri);
    }

    public function testFragmentContextUsesCurrentRequestAsParentAndUriArgument(): void
    {
        $parent = Request::create('/parent');
        $parent->attributes->set('_route', 'app.parent');
        $parent->attributes->set('_controller', 'App\\Controller\\Parent::index');
        $stack = new RequestStack();
        $stack->push($parent);

        // deliver() formats the message with the *parent* URI, because the sub-request is already popped.
        $exception = new RuntimeException(
            'Error when rendering "http://localhost/parent" (Status code is 404).',
            0,
            new HttpException(404),
        );

        $context = (new FragmentFailureContextFactory($stack))->fromException($exception, '/widget?x=1');

        $this->assertSame(404, $context->statusCode);
        $this->assertSame('/widget?x=1', $context->fragmentUri);
        $this->assertNull($context->route);
        $this->assertNull($context->controller);
        $this->assertSame('app.parent', $context->parentRoute);
        $this->assertSame('/parent', $context->parentUri);
    }

    public function testFragmentContextFromControllerReferenceGeneratesUnsignedUri(): void
    {
        $parent = Request::create('/parent');
        $stack  = new RequestStack();
        $stack->push($parent);
        $reference = new ControllerReference('App\\Controller\\Widget::index', ['id' => 3]);

        $generator = $this->createMock(FragmentUriGeneratorInterface::class);
        $generator->expects($this->once())
            ->method('generate')
            ->with($reference, $parent, false, false, false)
            ->willReturn('/_fragment?_path=id%3D3');

        $context = (new FragmentFailureContextFactory($stack, $generator))->fromException(new LogicException('boom'), $reference);

        $this->assertSame(500, $context->statusCode);
        $this->assertSame('/_fragment?_path=id%3D3', $context->fragmentUri);
        $this->assertSame('App\\Controller\\Widget::index', $context->controller);
        $this->assertNull($context->parentRoute);
        $this->assertSame('/parent', $context->parentUri);
    }

    public function testFragmentUriIsNullWhenGeneratorFailsOrIsMissing(): void
    {
        $stack     = new RequestStack();
        $reference = new ControllerReference('App\\Controller\\Widget::index');

        $generator = $this->createMock(FragmentUriGeneratorInterface::class);
        $generator->method('generate')->willThrowException(new LogicException('no request'));

        $withFailingGenerator = (new FragmentFailureContextFactory($stack, $generator))->fromException(new LogicException('boom'), $reference);
        $withoutGenerator     = (new FragmentFailureContextFactory($stack))->fromException(new LogicException('boom'), $reference);

        $this->assertNull($withFailingGenerator->fragmentUri);
        $this->assertNull($withFailingGenerator->parentUri);
        $this->assertNull($withoutGenerator->fragmentUri);
        $this->assertSame('App\\Controller\\Widget::index', $withoutGenerator->controller);
    }
}
