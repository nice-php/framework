<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\RouteCollector;

use Nice\Router\RouteCollector\CachedCollector;
use Nice\Router\RouteCollector;

class RouteCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic functionality
     */
    public function testFunctionality()
    {
        $parser = $this->getMock('FastRoute\RouteParser');
        $parser->expects($this->once())->method('parse')
            ->will($this->returnArgument(1));
        $generator = $this->getMock('FastRoute\DataGenerator');
        $generator->expects($this->once())->method('addRoute');
        $generator->expects($this->once())->method('getData');

        $collector = new ConcreteRouteCollector($parser, $generator);
        
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
        $this->addRoute('GET', '/', 'handler0');
    }
}