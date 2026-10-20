<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\DependencyInjection;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\HttpKernel\Fragment\ResilientFragmentHandler;
use Nowo\FragmentKitBundle\Null\NullFragmentFailureReporter;
use Nowo\FragmentKitBundle\Sentry\SentryFragmentFailureReporter;
use Sentry\State\HubInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Loads Fragment Kit services and applies enabled / Sentry reporter wiring.
 */
final class FragmentKitExtension extends Extension
{
    /**
     * Returns the configuration alias (nowo_fragment_kit).
     */
    public function getAlias(): string
    {
        return Configuration::ALIAS;
    }

    /**
     * Exposes the processed configuration as parameters, loads the services and wires the failure reporter (handler removed when disabled).
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(Configuration::ALIAS . '.enabled', $config['enabled']);
        $container->setParameter(Configuration::ALIAS . '.fallback.template', $config['fallback']['template'] ?: null);
        $container->setParameter(Configuration::ALIAS . '.sentry', $config['sentry']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if (!$config['enabled']) {
            $container->removeDefinition(ResilientFragmentHandler::class);

            return;
        }

        $this->registerFailureReporter($container, $config['sentry']);
    }

    /**
     * @param array{enabled: bool, level: string} $sentryConfig
     */
    private function registerFailureReporter(ContainerBuilder $container, array $sentryConfig): void
    {
        if ($sentryConfig['enabled'] && interface_exists(HubInterface::class)) {
            $container->setAlias(
                FragmentFailureReporterInterface::class,
                SentryFragmentFailureReporter::class,
            );

            return;
        }

        $container->setAlias(
            FragmentFailureReporterInterface::class,
            NullFragmentFailureReporter::class,
        );

        if ($container->hasDefinition(SentryFragmentFailureReporter::class)) {
            $container->getDefinition(SentryFragmentFailureReporter::class)
                ->setAutowired(false)
                ->setArgument('$hub', null);
        }
    }
}
