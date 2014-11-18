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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterListenersPass;

class DefaultInitializer implements ContainerInitializerInterface
{
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
        $container = $container = $this->getContainerBuilder();
        $container->addObjectResource($application);
        $container->setParameter('app.env', $application->getEnvironment());
        $container->setParameter('app.debug', $application->isDebug());
        $container->setParameter('app.cache', $application->isCacheEnabled());
        $container->setParameter('app.root_dir', $application->getRootDir());
        $container->setParameter('app.cache_dir', $application->getCacheDir());
        $container->setParameter('app.log_dir', $application->getLogDir());

        $container->register('event_dispatcher', 'Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher')
            ->setArguments(array(new Reference('service_container')));

        $container->register('app', 'Symfony\Component\HttpKernel\HttpKernelInterface')
            ->setSynthetic(true);

        $container->register('request', 'Symfony\Componenet\HttpKernel\Request')
            ->setSynthetic(true)
            ->setSynchronized(true)
            ->setScope('request');

        $container->addScope(new Scope('request'));

        $extensionAliases = array();
        foreach ($extensions as $extension) {
            $container->registerExtension($extension);
            $extensionAliases[] = $extension->getAlias();
        }

        $container->addCompilerPass(new MergeExtensionConfigurationPass($extensionAliases));
        $container->addCompilerPass(new RegisterListenersPass());

        foreach ($compilerPasses as $pass) {
            if (is_array($pass)) {
                $container->addCompilerPass($pass[0], $pass[1]);
            } else {
                $container->addCompilerPass($pass);
            }
        }

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
