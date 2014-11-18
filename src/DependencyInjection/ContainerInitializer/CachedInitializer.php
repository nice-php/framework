<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection\ContainerInitializer;

use Nice\Application;
use Nice\DependencyInjection\ContainerInitializerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class CachedInitializer implements ContainerInitializerInterface
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var \Nice\DependencyInjection\ContainerInitializerInterface
     */
    private $wrappedInitializer;

    /**
     * Constructor
     *
     * @param ContainerInitializerInterface $wrappedInitializer
     * @param string                        $cacheDir
     */
    public function __construct(ContainerInitializerInterface $wrappedInitializer, $cacheDir)
    {
        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new \RuntimeException(sprintf("Unable to create the cache directory (%s)", $cacheDir));
            }
        } elseif (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in the cache directory (%s)", $cacheDir));
        }

        $this->cacheDir = $cacheDir;
        $this->wrappedInitializer = $wrappedInitializer;
    }

    /**
     * Returns a fully built, ready to use Container
     *
     * @param Application                   $application
     * @param array|ExtensionInterface[]    $extensions
     * @param array|CompilerPassInterface[] $compilerPasses
     *
     * @return ContainerInterface
     */
    public function initializeContainer(Application $application, array $extensions = array(), array $compilerPasses = array())
    {
        $class = $this->getContainerClass($application);
        $cache = new ConfigCache($this->cacheDir.'/'.$class.'.php', $application->isDebug());
        if (!$cache->isFresh()) {
            $container = $this->wrappedInitializer->initializeContainer($application, $extensions, $compilerPasses);

            $this->dumpContainer($cache, $container, $class, 'Container');
        }

        require_once $cache;

        return new $class();
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass(Application $application)
    {
        return ucfirst($application->getEnvironment()).($application->isDebug() ? 'Debug' : '').'ProjectContainer';
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache      $cache     The config cache
     * @param ContainerBuilder $container The service container
     * @param string           $class     The name of the class to generate
     * @param string           $baseClass The name of the container's base class
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        $dumper  = new PhpDumper($container);
        $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));

        $cache->write($content, $container->getResources());
    }
}
