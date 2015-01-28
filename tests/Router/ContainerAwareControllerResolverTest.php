<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router;

use Nice\Router\ContainerAwareControllerResolver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ContainerAwareControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the ContainerAwareControllerResolver properly injects the container
     */
    public function testInjectsContainer()
    {
        $request = new Request();
        $request->attributes->set('_controller', 'Nice\Tests\Router\ContainerAwareController::someAction');
        $resolver = new ContainerAwareControllerResolver();

        $container = new Container();
        $resolver->setContainer($container);

        $controller = $resolver->getController($request);

        $this->assertCount(2, $controller);
        $this->assertSame($container, $controller[0]->getContainer());
    }

    /**
     * Test that the ControllerResolver knows how to get services to be used as controllers
     */
    public function testServicesAsControllers()
    {
        $request = new Request();
        $request->attributes->set('_controller', 'nice.some_controller:someAction');
        $resolver = new ContainerAwareControllerResolver();

        $container = new Container();
        $service = new ContainerAwareController();
        $container->set('nice.some_controller', $service);
        $resolver->setContainer($container);

        $controller = $resolver->getController($request);

        $this->assertCount(2, $controller);
        $this->assertSame($service, $controller[0]);
    }
}

class ContainerAwareController implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * An action
     */
    public function someAction()
    {
        // no op
    }
}
