<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Sets up routing related services
 */
class RouterExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container->register('router.parser', 'FastRoute\RouteParser\Std');
        $container->register('router.data_generator.strategy', 'FastRoute\DataGenerator\GroupCountBased');
        $container->register('router.data_generator', 'Nice\Router\NamedDataGenerator\HandlerWrapperGenerator')
            ->addArgument(new Reference('router.data_generator.strategy'));

        $container->setParameter('router.collector.class', 'Nice\Router\RouteCollector\SimpleCollector');

        $container->register('routes', 'Closure')
            ->setSynthetic(true);

        $container->register('router.collector', '%router.collector.class%')
            ->addArgument(new Reference('router.parser'))
            ->addArgument(new Reference('router.data_generator'))
            ->addArgument(new Reference('routes'));

        $container->register('router.dispatcher_factory', 'Nice\Router\DispatcherFactory\GroupCountBasedFactory')
            ->addArgument(new Reference('router.collector'));

        $container->register('router.dispatcher', 'FastRoute\Dispatcher')
            ->setFactory(array(new Reference('router.dispatcher_factory'), 'create'));

        $container->register('router.dispatcher_subscriber', 'Nice\Router\RouterSubscriber')
            ->addArgument(new Reference('router.dispatcher'))
            ->addTag('kernel.event_subscriber');

        $container->register('router.wrapped_handler_subscriber', 'Nice\Router\WrappedHandlerSubscriber')
            ->addTag('kernel.event_subscriber');

        $container->register('router.controller_resolver', 'Nice\Router\ContainerAwareControllerResolver')
            ->addMethodCall('setContainer', array(new Reference('service_container')));

        $container->register('router.url_generator.data_generator', 'Nice\Router\UrlGenerator\GroupCountBasedDataGenerator')
            ->addArgument(new Reference('router.collector'));

        $container->register('router.url_generator', 'Nice\Router\UrlGenerator\SimpleUrlGenerator')
            ->addArgument(new Reference('router.url_generator.data_generator'))
            ->addMethodCall('setRequest', array(new Reference(
                    'request',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                    false
                )))
            ->setPublic(true);

        $container->register('http_kernel', 'Symfony\Component\HttpKernel\HttpKernel')
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument(new Reference('router.controller_resolver'))
            ->setPublic(true);
    }
}
