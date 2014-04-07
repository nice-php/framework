<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Scope;

/**
 * Sets up Cache services
 */
class CacheExtension extends Extension
{
    /**
     * @var array
     */
    private $options = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Returns extension configuration
     *
     * @param array            $config    $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return SecurityConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new CacheConfiguration();
    }
    
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configs[] = $this->options;
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        
        foreach ($config['connections'] as $index => $connection) {
            $name = $connection['name'];
            
            $driverDefinition = $container->register('cache.' . $name . '.driver');
            $driverDefinition->setClass('Redis');
            $driverDefinition->addMethodCall('pconnect', array(
                    '/tmp/redis.sock'
                ));
            
            $definition = $container->register('cache.' . $name);
            $definition->setClass('Doctrine\Common\Cache\RedisCache');
            $definition->addMethodCall('setRedis', array(new Reference('cache.' . $name . '.driver')));
            $definition->addMethodCall('setNamespace', array($connection['namespace']));
        }
    }
}
