<?php

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
            ->scalarNode('username')->isRequired()->end()
            ->scalarNode('password')->isRequired()->end()
            ->scalarNode('login_path')->defaultValue('/login')->end()
            ->scalarNode('success_path')->defaultValue('/')->end()
            ->scalarNode('logout_path')->defaultValue('/logout')->end()
            ->scalarNode('token_session_key')->defaultValue('__nice_is_authenticated')->end()
            ->end();
        
        return $treeBuilder;
    }
}