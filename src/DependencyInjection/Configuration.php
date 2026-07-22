<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration tree for `nowo_fragment_kit`.
 */
class Configuration implements ConfigurationInterface
{
    public const ALIAS = 'nowo_fragment_kit';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ALIAS);
        $root        = $treeBuilder->getRootNode();

        $root
            ->children()
                ->booleanNode('enabled')
                    ->info('When false, the bundle does not decorate fragment.handler.')
                    ->defaultTrue()
                ->end()
                ->arrayNode('fallback')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')
                            ->info('Twig template rendered when a fragment fails and ignore_errors is true. Null or empty = return an empty string.')
                            ->defaultValue('@NowoFragmentKitBundle/fragment_failure.html.twig')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sentry')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Report suppressed fragment failures to Sentry (requires sentry/sentry-symfony).')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('level')
                            ->info('Sentry severity: debug, info, warning, error, fatal.')
                            ->defaultValue('warning')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
