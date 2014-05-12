<?php

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineKeyValueExtension extends Extension
{
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
        // TODO: Add configuration for cache.default service
        $container->register('doctrine.key_value.storage', 'Doctrine\KeyValueStore\Storage\DoctrineCacheStorage')
            ->setPublic(false)
            ->addArgument(new Reference('cache.default'));

        $container->register('doctrine.key_value.metadata.annotation.reader', 'Doctrine\Common\Annotations\AnnotationReader')
            ->setPublic(false);

        $container->register('doctrine.key_value.metadata.annotation', 'Doctrine\KeyValueStore\Mapping\AnnotationDriver')
            ->setPublic(false)
            ->addArgument(new Reference('doctrine.key_value.metadata.annotation.reader'));

        $container->register('doctrine.key_value.configuration', 'Doctrine\KeyValueStore\Configuration')
            ->setPublic(false)
            ->addMethodCall('setMappingDriverImpl', array(new Reference('doctrine.key_value.metadata.annotation')))
            ->addMethodCall('setMetadataCache', array(new Reference('cache.default')));

        $container->register('doctrine.key_value.entity_manager', 'Doctrine\KeyValueStore\EntityManager')
            ->addArgument(new Reference('doctrine.key_value.storage'))
            ->addArgument(new Reference('doctrine.key_value.configuration'));
    }
}