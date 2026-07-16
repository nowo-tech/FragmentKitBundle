<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\Model;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(FragmentFailureContext::class)]
final class FragmentFailureContextTest extends TestCase
{
    public function testConstructWithDefaults(): void
    {
        $exception = new RuntimeException('x');
        $context   = new FragmentFailureContext($exception, 503);

        self::assertSame($exception, $context->exception);
        self::assertSame(503, $context->statusCode);
        self::assertNull($context->fragmentUri);
        self::assertNull($context->route);
        self::assertNull($context->parentRoute);
        self::assertNull($context->parentUri);
        self::assertNull($context->controller);
    }
}
