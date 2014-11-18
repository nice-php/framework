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

class CacheConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cache');

        $rootNode
            ->children()
                ->arrayNode('connections')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('driver')
                                ->isRequired()
                                ->validate()
                                    ->ifNotInArray(array('redis', 'memcached', 'memcache', 'array'))
                                    ->thenInvalid('Invalid cache driver %s')
                                ->end()
                            ->end()
                            ->scalarNode('namespace')->defaultValue('nice:')->end()
                            ->arrayNode('options')->defaultValue(array())
                                ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
