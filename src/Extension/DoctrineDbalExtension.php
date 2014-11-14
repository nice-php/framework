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

class DoctrineDbalExtension extends Extension
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
     * @return DoctrineOrmConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new DoctrineDbalConfiguration();
    }
    
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs[] = $this->options;
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->register('doctrine.dbal.configuration', 'Doctrine\DBAL\Configuration');

        $container->register('doctrine.dbal.database_connection', 'Doctrine\DBAL\Connection')
            ->setFactoryClass('Doctrine\DBAL\DriverManager')
            ->setFactoryMethod('getConnection')
            ->addArgument($config['database'])
            ->addArgument(new Reference('doctrine.dbal.configuration'));
    }
}