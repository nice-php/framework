<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\DispatcherFactory;

use FastRoute\RouteCollector;
use Nice\Router\DispatcherFactory\CachedDispatcherFactory;

class CachedDispatcherFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Dispatcher creation
     */
    public function testCreateWithoutCacheCollects()
    {
        $filename = tempnam(null, '__test');
        $factory  = $this->getFactory($filename);

        $dispatcher = $factory->create();

        $this->assertNotEmpty(file_get_contents($filename));
        $this->assertInstanceOf('FastRoute\Dispatcher\GroupCountBased', $dispatcher);
    }

    /**
     * Test that routes are collected only once even with multiple dispatcher creations
     */
    public function testCreateTwiceCollectsOnce()
    {
        $filename = tempnam(null, '__test');
        $factory  = $this->getFactory($filename);

        $dispatcher = $factory->create();
        $dispatcher2 = $factory->create();

        $this->assertNotSame($dispatcher, $dispatcher2);
    }

    /**
     * @param $filename
     *
     * @return CachedDispatcherFactory
     */
    protected function getFactory($filename)
    {
        $routeCollector = $this->getMockBuilder('FastRoute\RouteCollector')
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects($this->once())
            ->method('addRoute');
        $routeCollector->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(array(array(), array())));

        $factory = new ConcreteCachedFactory($routeCollector, $filename, true);

        return $factory;
    }
}

class ConcreteCachedFactory extends CachedDispatcherFactory
{
    /**
     * Collect configured routes, actually doing the work
     *
     * Implement this method in a subclass
     *
     * @param RouteCollector $collector
     */
    protected function doCollectRoutes(RouteCollector $collector)
    {
        $collector->addRoute('GET', '/', 'handler0');
    }
}
