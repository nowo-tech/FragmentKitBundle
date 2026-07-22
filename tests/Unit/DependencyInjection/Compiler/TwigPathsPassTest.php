<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\DependencyInjection\Compiler;

use Nowo\FragmentKitBundle\DependencyInjection\Compiler\TwigPathsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use function dirname;

#[CoversClass(TwigPathsPass::class)]
final class TwigPathsPassTest extends TestCase
{
    public function testProcessAddsBundleViewsPath(): void
    {
        $container = new ContainerBuilder();
        $loader    = new Definition();
        $container->setDefinition('twig.loader.native', $loader);

        (new TwigPathsPass())->process($container);

        self::assertSame(
            [['addPath', [dirname(__DIR__, 4) . '/src/Resources/views', 'NowoFragmentKitBundle']]],
            $loader->getMethodCalls(),
        );
    }

    public function testProcessPrependsOverridePathWhenPresent(): void
    {
        $container  = new ContainerBuilder();
        $loader     = new Definition();
        $projectDir = sys_get_temp_dir() . '/fragment-kit-twig-paths-' . bin2hex(random_bytes(4));
        mkdir($projectDir . '/templates/bundles/NowoFragmentKitBundle', 0777, true);

        $container->setDefinition('twig.loader.native', $loader);
        $container->setParameter('kernel.project_dir', $projectDir);

        try {
            (new TwigPathsPass())->process($container);
        } finally {
            rmdir($projectDir . '/templates/bundles/NowoFragmentKitBundle');
            rmdir($projectDir . '/templates/bundles');
            rmdir($projectDir . '/templates');
            rmdir($projectDir);
        }

        self::assertSame(
            [
                ['prependPath', [$projectDir . '/templates/bundles/NowoFragmentKitBundle', 'NowoFragmentKitBundle']],
                ['addPath', [dirname(__DIR__, 4) . '/src/Resources/views', 'NowoFragmentKitBundle']],
            ],
            $loader->getMethodCalls(),
        );
    }

    public function testProcessUsesAliasWhenDefined(): void
    {
        $container = new ContainerBuilder();
        $loader    = new Definition();
        $container->setDefinition('twig.loader.native_filesystem', $loader);
        $container->setAlias('twig.loader.native', new Alias('twig.loader.native_filesystem'));

        (new TwigPathsPass())->process($container);

        self::assertSame('addPath', $loader->getMethodCalls()[0][0]);
    }

    public function testProcessResolvesAliasChain(): void
    {
        $container = new ContainerBuilder();
        $loader    = new Definition();
        $container->setDefinition('twig.loader.native_filesystem', $loader);
        $container->setAlias('twig.loader.mid', new Alias('twig.loader.native_filesystem'));
        $container->setAlias('twig.loader.native', new Alias('twig.loader.mid'));

        (new TwigPathsPass())->process($container);

        self::assertSame(
            [['addPath', [dirname(__DIR__, 4) . '/src/Resources/views', 'NowoFragmentKitBundle']]],
            $loader->getMethodCalls(),
        );
    }

    public function testProcessFallsBackToNativeFilesystemDefinition(): void
    {
        $container = new ContainerBuilder();
        $loader    = new Definition();
        $container->setDefinition('twig.loader.native_filesystem', $loader);

        (new TwigPathsPass())->process($container);

        self::assertSame(
            [['addPath', [dirname(__DIR__, 4) . '/src/Resources/views', 'NowoFragmentKitBundle']]],
            $loader->getMethodCalls(),
        );
    }

    public function testProcessFallsBackToFilesystemDefinition(): void
    {
        $container = new ContainerBuilder();
        $loader    = new Definition();
        $container->setDefinition('twig.loader.filesystem', $loader);

        (new TwigPathsPass())->process($container);

        self::assertSame(
            [['addPath', [dirname(__DIR__, 4) . '/src/Resources/views', 'NowoFragmentKitBundle']]],
            $loader->getMethodCalls(),
        );
    }

    public function testProcessIgnoresNonStringProjectDir(): void
    {
        $container = new ContainerBuilder();
        $loader    = new Definition();
        $container->setDefinition('twig.loader.native', $loader);
        $container->setParameter('kernel.project_dir', 123);

        (new TwigPathsPass())->process($container);

        self::assertSame(
            [['addPath', [dirname(__DIR__, 4) . '/src/Resources/views', 'NowoFragmentKitBundle']]],
            $loader->getMethodCalls(),
        );
    }

    public function testProcessSkipsWhenTwigLoaderMissing(): void
    {
        $container = new ContainerBuilder();

        (new TwigPathsPass())->process($container);

        self::assertFalse($container->hasDefinition('twig.loader.native'));
    }

    public function testProcessSkipsDanglingNativeAliasWithoutFallback(): void
    {
        $container = new ContainerBuilder();
        $container->setAlias('twig.loader.native', new Alias('twig.loader.missing'));

        (new TwigPathsPass())->process($container);

        self::assertFalse($container->hasDefinition('twig.loader.native'));
        self::assertFalse($container->hasDefinition('twig.loader.missing'));
    }
}
