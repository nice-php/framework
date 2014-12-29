<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Defines the contract any ConfigurationProvider must implement.
 *
 * A ConfigurationProvider can load the configuration for a ContainerBuilder from
 * a specific source.
 */
interface ConfigurationProviderInterface
{
    /**
     * Load the given ContainerBuilder with its configuration.
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function load(ContainerBuilder $container);
}
