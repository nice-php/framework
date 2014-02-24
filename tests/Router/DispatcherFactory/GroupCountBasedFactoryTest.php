<?php

namespace TylerSommer\Nice\Tests\Router\DispatcherFactory;

use FastRoute\RouteCollector;
use TylerSommer\Nice\Router\DispatcherFactory\GroupCountBasedFactory;

class GroupCountBasedFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Dispatcher creation
     */
    public function testCreate()
    {
        $routeCollector = $this->getMockBuilder('FastRoute\RouteCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array(array(), array())));
        $routeFactory = function (RouteCollector $collector) { };

        $factory = new GroupCountBasedFactory($routeCollector, $routeFactory);

        $dispatcher = $factory->create();

        $this->assertInstanceOf('FastRoute\Dispatcher\GroupCountBased', $dispatcher);
    }

    /**
     * Test that routes are collected only once even with multiple dispatcher creations
     */
    public function testCreateTwiceCollectsOnce()
    {
        $routeCollector = $this->getMockBuilder('FastRoute\RouteCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects($this->exactly(2))
            ->method('getData')
            ->will($this->returnValue(array(array(), array())));
        $called = 0;
        $routeFactory = function (RouteCollector $collector) use (&$called) {
            // TODO: Is there a better way to accomplish this?
            $called++;
        };

        $factory = new GroupCountBasedFactory($routeCollector, $routeFactory);

        $dispatcher = $factory->create();

        $this->assertInstanceOf('FastRoute\Dispatcher\GroupCountBased', $dispatcher);

        $factory->create();

        $this->assertEquals(1, $called);
    }
}
