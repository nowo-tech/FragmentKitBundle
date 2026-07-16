<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\Service;

use Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[CoversClass(FragmentFailureContextFactory::class)]
final class FragmentFailureContextFactoryTest extends TestCase
{
    public function testBuildsContextFromFragmentDeliverException(): void
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
}
