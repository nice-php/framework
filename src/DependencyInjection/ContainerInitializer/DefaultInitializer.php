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

class DefaultInitializer implements ContainerInitializerInterface
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     * 
     * @param string $environment
     * @param bool   $debug
     */
    public function __construct($environment, $debug = false)
    {
        $this->environment = $environment;
        $this->debug       = $debug;
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
        $container = $container = $this->getContainerBuilder();
        $container->addObjectResource($application);
        $container->setParameter('app.env', $application->getEnvironment());
        $container->setParameter('app.debug', $application->isDebug());
        $container->setParameter('app.root_dir', $application->getRootDir());
        $container->setParameter('app.cache_dir', $application->getCacheDir());
        $container->setParameter('app.log_dir', $application->getLogDir());

        $container->register('event_dispatcher', 'Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher')
            ->setArguments(array(new Reference('service_container')));

        $container->register('app', 'Symfony\Component\HttpKernel\HttpKernelInterface')
            ->setSynthetic(true);
        
        $container->register('request', 'Symfony\Componenet\HttpKernel\Request')
            ->setSynthetic(true);

        $extensions = array();
        foreach ($application->getRegisteredExtensions() as $extension) {
            $container->registerExtension($extension);
            $extensions[] = $extension->getAlias();
        }

        $container->addCompilerPass(new MergeExtensionConfigurationPass($extensions));
        $container->addCompilerPass(new RegisterListenersPass());

        $container->compile();
        
        return $container;
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