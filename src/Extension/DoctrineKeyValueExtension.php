<?php

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineKeyValueExtension extends Extension
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
        return new DoctrineKeyValueConfiguration();
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

        $this->configureStorage($config['key_value'], $container);
        $this->configureMetadataDriver($config['key_value'], $container);
        $this->configureEntityManager($config['key_value'], $container);
    }

    /**
     * Configure the data store.
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function configureStorage(array $config, ContainerBuilder $container)
    {
        $container->register('doctrine.key_value.storage', 'Doctrine\KeyValueStore\Storage\DoctrineCacheStorage')
            ->setPublic(false)
            ->addArgument(new Reference($config['cache_driver']));
    }

    /**
     * Configure the metadata driver.
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function configureMetadataDriver(array $config, ContainerBuilder $container)
    {
        $container->register('doctrine.key_value.metadata.annotation.reader', 'Doctrine\Common\Annotations\AnnotationReader')
            ->setPublic(false);

        $container->register('doctrine.key_value.metadata.annotation', 'Doctrine\KeyValueStore\Mapping\AnnotationDriver')
            ->setPublic(false)
            ->addArgument(new Reference('doctrine.key_value.metadata.annotation.reader'));
    }

    /**
     * Configure the entity manager.
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function configureEntityManager(array $config, ContainerBuilder $container)
    {
        $container->register('doctrine.key_value.configuration', 'Doctrine\KeyValueStore\Configuration')
            ->addMethodCall('setMappingDriverImpl', array(new Reference('doctrine.key_value.metadata.annotation')))
            ->addMethodCall('setMetadataCache', array(new Reference('cache.default')));

        $container->register('doctrine.key_value.entity_manager', 'Doctrine\KeyValueStore\EntityManager')
            ->addArgument(new Reference('doctrine.key_value.storage'))
            ->addArgument(new Reference('doctrine.key_value.configuration'));
    }
}