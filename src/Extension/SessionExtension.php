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

/**
 * Sets up Session related services
 */
class SessionExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container->register('session', 'Symfony\Component\HttpFoundation\Session\Session')
            ->setPublic(true);

        $container->register('session.session_subscriber', 'Nice\Session\SessionSubscriber')
            ->addArgument(new Reference('service_container'))
            ->addTag('kernel.event_subscriber');
    }
}
