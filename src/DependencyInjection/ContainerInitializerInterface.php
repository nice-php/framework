<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection;

use Nice\Application;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Defines the contract any ContainerInitializer must follow
 *
 * A ContainerInitializer is responsible for creating a DependencyInjection Container
 * and readying it for use by a Nice Application
 */
interface ContainerInitializerInterface
{
    /**
     * Returns a fully built, ready to use Container
     *
     * A $configLoader should accept a Symfony\Component\Config\LoaderInterface as its only argument.
     * This can be used to register application configuration from a variety of sources.
     *
     * @param Application                   $application
     * @param array|ExtensionInterface[]    $extensions
     * @param array|CompilerPassInterface[] $compilerPasses
     * @param callable                      $configLoader
     *
     * @return ContainerInterface
     */
    public function initializeContainer(Application $application, array $extensions = array(), array $compilerPasses = array(), callable $configLoader = null);
}
