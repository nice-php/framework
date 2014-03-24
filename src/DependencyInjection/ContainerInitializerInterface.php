<?php

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