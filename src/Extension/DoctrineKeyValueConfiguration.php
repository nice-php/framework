<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DoctrineKeyValueConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('doctrine');

        $rootNode
            ->children()
                ->arrayNode('key_value')
                    ->isRequired()
                    ->children()
                        ->scalarNode('cache_driver')->defaultValue('default')->end()
                        ->arrayNode('mapping')
                            ->isRequired()
                            ->children()
                                ->arrayNode('paths')
                                    ->isRequired()
                                    ->requiresAtLeastOneElement()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}