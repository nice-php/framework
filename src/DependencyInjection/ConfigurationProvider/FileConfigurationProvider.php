<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection\ConfigurationProvider;

use Nice\DependencyInjection\ConfigurationProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * A ConfigurationProvider that can load from file-based sources.
 */
class FileConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var string
     */
    protected $configDir;

    /**
     * @var string
     */
    protected $configFile;

    /**
     * Constructor
     *
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        $this->configDir = dirname($configFile);
        $this->configFile = basename($configFile);
    }

    /**
     * Load the given ContainerBuilder with its configuration.
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function load(ContainerBuilder $container)
    {
        $loader = $this->getContainerLoader($container);

        $loader->load($this->configFile);
    }

    /**
     * Returns a loader for the container.
     *
     * This loader can be used to load extension configurations from various sources.
     *
     * @param ContainerInterface $container The service container
     *
     * @return LoaderInterface
     */
    protected function getContainerLoader(ContainerInterface $container)
    {
        $locator = new FileLocator($this->configDir);
        $resolver = new LoaderResolver(array(
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
        ));

        return new DelegatingLoader($resolver);
    }
}
