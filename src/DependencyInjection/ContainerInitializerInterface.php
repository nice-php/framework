<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection;

use Nice\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface ContainerInitializerInterface
{
    /**
     * Returns a fully built, ready to use Container
     *
     * @param Application $application
     *
     * @return ContainerInterface
     */
    public function initializeContainer(Application $application);
}
