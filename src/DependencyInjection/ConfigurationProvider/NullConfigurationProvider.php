<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection\ConfigurationProvider;

use Nice\DependencyInjection\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * A default, no-operation ConfigurationProvider
 */
class NullConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * Load the given ContainerBuilder with its configuration.
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function load(ContainerBuilder $container)
    {
        // no op
    }
}
