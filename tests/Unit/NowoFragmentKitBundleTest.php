<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit;

use Nowo\FragmentKitBundle\DependencyInjection\FragmentKitExtension;
use Nowo\FragmentKitBundle\NowoFragmentKitBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NowoFragmentKitBundle::class)]
final class NowoFragmentKitBundleTest extends TestCase
{
    public function testGetContainerExtensionReturnsFragmentKitExtension(): void
    {
        $bundle = new NowoFragmentKitBundle();

        self::assertInstanceOf(FragmentKitExtension::class, $bundle->getContainerExtension());
        self::assertSame($bundle->getContainerExtension(), $bundle->getContainerExtension());
    }
}
