<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Scope;

/**
 * Sets up FastRoute services
 */
class RouterExtension extends Extension
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
        $container->register('router.parser', 'FastRoute\RouteParser\Std');
        $container->register('router.data_generator', 'FastRoute\DataGenerator\GroupCountBased');
        

        if (!$container->hasDefinition('router.collector')) {
            $container->register('routes', 'Closure')
                ->setSynthetic(true);

            $container->register('router.collector', 'Nice\Router\RouteCollector\SimpleCollector')
                ->addArgument(new Reference('router.parser'))
                ->addArgument(new Reference('router.data_generator'))
                ->addArgument(new Reference('routes'));
        }

        $container->register('router.dispatcher_factory', 'Nice\Router\DispatcherFactory\GroupCountBasedFactory')
            ->addArgument(new Reference('router.collector'));

        $container->register('router.dispatcher', 'FastRoute\Dispatcher')
            ->setFactoryService('router.dispatcher_factory')
            ->setFactoryMethod('create');

        $container->register('router.dispatcher_subscriber', 'Nice\Router\RouterSubscriber')
            ->addArgument(new Reference('router.dispatcher'))
            ->addTag('kernel.event_subscriber');

        $container->register('router.controller_resolver', 'Nice\Router\ContainerAwareControllerResolver')
            ->addMethodCall('setContainer', array(new Reference('service_container')));

        $container->register('http_kernel', 'Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel')
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument(new Reference('service_container'))
            ->addArgument(new Reference('router.controller_resolver'));

        $container->addScope(new Scope('request'));
    }
}
