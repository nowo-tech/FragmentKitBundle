<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit;

use Nowo\FragmentKitBundle\DependencyInjection\Compiler\TwigPathsPass;
use Nowo\FragmentKitBundle\DependencyInjection\FragmentKitExtension;
use Nowo\FragmentKitBundle\NowoFragmentKitBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(NowoFragmentKitBundle::class)]
final class NowoFragmentKitBundleTest extends TestCase
{
    public function testGetContainerExtensionReturnsFragmentKitExtension(): void
    {
        $bundle = new NowoFragmentKitBundle();

        self::assertInstanceOf(FragmentKitExtension::class, $bundle->getContainerExtension());
        self::assertSame($bundle->getContainerExtension(), $bundle->getContainerExtension());
    }

    public function testBuildRegistersTwigPathsPass(): void
    {
        $container = new ContainerBuilder();

        (new NowoFragmentKitBundle())->build($container);

        $found = false;
        foreach ($container->getCompilerPassConfig()->getPasses() as $pass) {
            if ($pass instanceof TwigPathsPass) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'TwigPathsPass was not registered.');
    }
}
