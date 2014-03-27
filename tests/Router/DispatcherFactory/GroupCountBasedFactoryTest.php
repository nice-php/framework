<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\DispatcherFactory;

use Nice\Router\DispatcherFactory\GroupCountBasedFactory;

class GroupCountBasedFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Dispatcher creation
     */
    public function testCreate()
    {
        $routeCollector = $this->getMockForAbstractClass('Nice\Router\RouteCollectorInterface');
        $routeCollector->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array(array(), array())));

        $factory = new GroupCountBasedFactory($routeCollector);

        $dispatcher = $factory->create();

        $this->assertInstanceOf('FastRoute\Dispatcher\GroupCountBased', $dispatcher);
    }
}
