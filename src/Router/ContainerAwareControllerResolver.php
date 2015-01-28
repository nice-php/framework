<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

/**
 * Extends the basic ControllerResolver to support ContainerAware controllers and controllers as services
 */
class ContainerAwareControllerResolver extends ControllerResolver implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return callable
     */
    protected function createController($controller)
    {
        $parts = explode(':', $controller);
        if (count($parts) === 2) {
            $service = $this->container->get($parts[0]);

            return array($service, $parts[1]);
        }

        $controller = parent::createController($controller);
        if ($controller[0] instanceof ContainerAwareInterface) {
            $controller[0]->setContainer($this->container);
        }

        return $controller;
    }
}
