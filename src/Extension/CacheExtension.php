<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Sets up Doctrine Cache services
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
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return CacheConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new CacheConfiguration();
    }

    /**
     * Loads a specific configuration.
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs[] = $this->options;
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['connections'] as $name => $cacheConfig) {
            $cacheConfig['name'] = $name;
            switch ($cacheConfig['driver']) {
                case 'redis':
                    $this->configureRedisCache($cacheConfig, $container);

                    break;

                case 'memcached':
                case 'memcache':
                    $this->configureMemcachedCache($cacheConfig, $container);

                    break;

                case 'array':
                    $this->configureArrayCache($cacheConfig, $container);

                    break;
            }
        }
    }

    private function configureRedisCache(array $cacheConfig, ContainerBuilder $container)
    {
        $defaults = array(
            'host' => '127.0.0.1',
            'socket' => null,
            'port' => 6379,
            'database' => 0,
            'persistent' => true,
            'timeout' => 0,
        );

        $options = array_merge($defaults, $cacheConfig['options']);
        if (isset($options['socket'])) {
            $options['host'] = $options['socket'];
            $options['port'] = null;
        }

        $name = $cacheConfig['name'];
        $cacheService = 'cache.'.$name;
        $driverService = $cacheService.'.driver';

        $driverDefinition = $container->register($driverService);
        $driverDefinition->setClass('Redis');
        $driverDefinition->addMethodCall($options['persistent'] ? 'pconnect' : 'connect', array(
                $options['host'],
                $options['port'],
                $options['timeout'],
            ));

        $definition = $container->register($cacheService);
        $definition->setClass('Doctrine\Common\Cache\RedisCache');
        $definition->addMethodCall('setRedis', array(new Reference($driverService)));
        $definition->addMethodCall('setNamespace', array($cacheConfig['namespace']));
    }

    private function configureMemcachedCache(array $cacheConfig, ContainerBuilder $container)
    {
        $defaults = array(
            'host' => '127.0.0.1',
            'socket' => null,
            'port' => 11211,
        );

        $options = array_merge($defaults, $cacheConfig['options']);
        if (isset($options['socket'])) {
            $options['host'] = $options['socket'];
            $options['port'] = null;
        }

        $cacheService = 'cache.'.$cacheConfig['name'];
        $driverService = $cacheService.'.driver';

        $driverDefinition = $container->register($driverService);
        $driverDefinition->addMethodCall('addServer', array(
                $options['host'],
                $options['port'],
            ));

        $cacheDefinition = $container->register($cacheService);
        $cacheDefinition->addMethodCall('setNamespace', array($cacheConfig['namespace']));

        if ($cacheConfig['driver'] === 'memcache') {
            $driverDefinition->setClass('Memcache');
            $cacheDefinition->setClass('Doctrine\Common\Cache\MemcacheCache');
            $cacheDefinition->addMethodCall('setMemcache', array(new Reference($driverService)));
        } else {
            $driverDefinition->setClass('Memcached');
            $cacheDefinition->setClass('Doctrine\Common\Cache\MemcachedCache');
            $cacheDefinition->addMethodCall('setMemcached', array(new Reference($driverService)));
        }
    }

    private function configureArrayCache(array $cacheConfig, ContainerBuilder $container)
    {
        $name = $cacheConfig['name'];
        $cacheService = 'cache.'.$name;

        $definition = $container->register($cacheService);
        $definition->setClass('Doctrine\Common\Cache\ArrayCache');
        $definition->addMethodCall('setNamespace', array($cacheConfig['namespace']));
    }
}
