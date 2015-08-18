<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\RouteCollector;

use Nice\Router\RouteCollector;

class RouteCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic functionality
     */
    public function testFunctionality()
    {
        $parser = $this->getMock('FastRoute\RouteParser');
        $parser->expects($this->exactly(7))->method('parse')
            ->will($this->returnCallback(function($route) {
                return array($route);
            }));
        $generator = $this->getMockForAbstractClass('Nice\Router\NamedDataGeneratorInterface');
        $generator->expects($this->exactly(2))->method('addRoute');
        $generator->expects($this->exactly(5))->method('addNamedRoute');
        $generator->expects($this->once())->method('getData');

        $collector = new ConcreteRouteCollector($parser, $generator);

        $collector->getData();
    }

    /**
     * Test basic functionality
     */
    public function testExceptionIfNotNamedDataGenerator()
    {
        $parser = $this->getMock('FastRoute\RouteParser');
        $parser->expects($this->atLeastOnce())
            ->method('parse')
            ->will($this->returnValue(array()));
        $generator = $this->getMockForAbstractClass('FastRoute\DataGenerator');

        $collector = new ConcreteRouteCollector($parser, $generator);

        $this->setExpectedException('RuntimeException', 'The injected generator does not support named routes');

        $collector->getData();
    }
}

class ConcreteRouteCollector extends RouteCollector
{
    /**
     * Perform any collection
     *
     * @return void
     */
    protected function collectRoutes()
    {
        $this->map('/', null, 'handler0');
        $this->map('/foo', null, 'handler1');
        $this->map('/test', 'test', 'handler2');
        $this->map('/bar', 'bar', 'handler3');
        $this->map('/testing', 'testing_home', 'handler4');
        $this->map('/user/{id}/update', 'users', 'handler5', array('POST', 'PATCH'));
    }
}
