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

class SecurityConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('security');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('firewall')->isRequired()->end()
            ->arrayNode('authenticator')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                        ->validate()
                            ->ifNotInArray(array('username', 'closure'))
                            ->thenInvalid('Invalid security authenticator %s')
                        ->end()
                    ->end()
                    ->scalarNode('username')->end()
                    ->scalarNode('password')->end()
                ->end()
            ->end()
            ->scalarNode('login_path')->defaultValue('/login')->end()
            ->scalarNode('success_path')->defaultValue('/')->end()
            ->scalarNode('logout_path')->defaultValue('/logout')->end()
            ->scalarNode('token_session_key')->defaultValue('__nice.is_authenticated')->end()
            ->end();

        return $treeBuilder;
    }
}
