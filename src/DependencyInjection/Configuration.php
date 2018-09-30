<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('okvpn_datadog');

        $rootNode->children()
            ->arrayNode('handle_exceptions')
                ->children()
                    ->enumNode('exception')
                        ->values(['all', 'uncatched', 'none'])
                        ->defaultValue('all')
                    ->end()
                    ->arrayNode('skip_instanceof')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_capture')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_hash')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_wildcard')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_command')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('dedup_path')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('artifacts_path')
                        ->defaultNull()
                    ->end()
                    ->integerNode('dedup_keep_time')
                        ->defaultNull()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('host')
                ->defaultValue('127.0.0.1')
            ->end()
            ->integerNode('port')
                ->min(1)->max(65535)
                ->defaultValue(8125)
            ->end()
            ->scalarNode('namespace')
                ->cannotBeEmpty()
            ->end()
            ->arrayNode('tags')
                ->prototype('scalar')->end()
            ->end()
            ->booleanNode('doctrine')
                ->defaultTrue()
            ->end()
            ->booleanNode('enable')
                ->defaultFalse()
            ->end();

        return $treeBuilder;
    }
}
