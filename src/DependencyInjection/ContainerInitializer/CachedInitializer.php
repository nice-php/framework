<?php

namespace Nice\DependencyInjection\ContainerInitializer;

use Nice\Application;
use Nice\DependencyInjection\ContainerInitializerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterListenersPass;

class CachedInitializer implements ContainerInitializerInterface
{
    /**
     * @var
     */
    private $cacheDir;

    /**
     * @var
     */
    private $environment;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var \Nice\DependencyInjection\ContainerInitializerInterface
     */
    private $wrappedInitializer;

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
     * @param Application $application
     *
     * @return ContainerInterface
     */
    public function initializeContainer(Application $application)
    {
        $class = $this->getContainerClass();
        $cache = new ConfigCache($this->cacheDir . '/' . $class . '.php', $this->debug);
        if (!$cache->isFresh()) {
            $container = $this->wrappedInitializer->initializeContainer($application);
            
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
    protected function getContainerClass()
    {
        return ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
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

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        return new ContainerBuilder();
    }
}