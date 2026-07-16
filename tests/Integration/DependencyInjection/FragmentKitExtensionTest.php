<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Integration\DependencyInjection;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\DependencyInjection\FragmentKitExtension;
use Nowo\FragmentKitBundle\HttpKernel\Fragment\ResilientFragmentHandler;
use Nowo\FragmentKitBundle\Null\NullFragmentFailureReporter;
use Nowo\FragmentKitBundle\Service\FragmentFailureRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Ensures the DI extension loads services and respects enabled / sentry flags.
 */
#[CoversClass(FragmentKitExtension::class)]
final class FragmentKitExtensionTest extends TestCase
{
    public function testGetAliasMatchesDocumentedConfigurationRoot(): void
    {
        $extension = new FragmentKitExtension();

        self::assertSame('nowo_fragment_kit', $extension->getAlias());
    }

    public function testLoadRegistersHandlerAndParameters(): void
    {
        $container = new ContainerBuilder();
        $extension = new FragmentKitExtension();
        $extension->load([[]], $container);

        self::assertTrue($container->getParameter('nowo_fragment_kit.enabled'));
        self::assertSame(
            '@NowoFragmentKit/fragment_failure.html.twig',
            $container->getParameter('nowo_fragment_kit.fallback.template'),
        );
        self::assertTrue($container->hasDefinition(ResilientFragmentHandler::class));
        self::assertTrue($container->hasDefinition(FragmentFailureRenderer::class));
        self::assertTrue($container->hasAlias(FragmentFailureReporterInterface::class));

        $expectedReporter = interface_exists(\Sentry\State\HubInterface::class)
            ? \Nowo\FragmentKitBundle\Sentry\SentryFragmentFailureReporter::class
            : NullFragmentFailureReporter::class;

        self::assertSame(
            $expectedReporter,
            (string) $container->getAlias(FragmentFailureReporterInterface::class),
        );
    }

    public function testLoadWhenDisabledRemovesHandler(): void
    {
        $container = new ContainerBuilder();
        $extension = new FragmentKitExtension();
        $extension->load([['enabled' => false]], $container);

        self::assertFalse($container->getParameter('nowo_fragment_kit.enabled'));
        self::assertFalse($container->hasDefinition(ResilientFragmentHandler::class));
    }

    public function testLoadWithCustomFallbackTemplate(): void
    {
        $container = new ContainerBuilder();
        $extension = new FragmentKitExtension();
        $extension->load([
            [
                'fallback' => [
                    'template' => 'fragment/unavailable.html.twig',
                ],
                'sentry' => [
                    'enabled' => false,
                    'level'   => 'error',
                ],
            ],
        ], $container);

        self::assertSame(
            'fragment/unavailable.html.twig',
            $container->getParameter('nowo_fragment_kit.fallback.template'),
        );
        self::assertSame(
            ['enabled' => false, 'level' => 'error'],
            $container->getParameter('nowo_fragment_kit.sentry'),
        );
        self::assertSame(
            NullFragmentFailureReporter::class,
            (string) $container->getAlias(FragmentFailureReporterInterface::class),
        );
    }
}
