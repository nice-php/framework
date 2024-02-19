<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\NamedDataGenerator;

use FastRoute\DataGenerator;
use PHPUnit\Framework\TestCase;
use Nice\Router\NamedDataGenerator\HandlerWrapperGenerator;

class HandlerWrapperGeneratorTest extends TestCase
{
    /**
     * Tests addRoute functionality
     */
    public function testAddRoute()
    {
        $wrappedGenerator = new ConcreteGenerator();
        $generator = new HandlerWrapperGenerator($wrappedGenerator);

        $generator->addRoute('GET', '/', 'handler0');

        $data = $generator->getData();

        $this->assertCount(1, $data);
        $this->assertEquals(array('GET', '/', 'handler0'), $data[0]);
    }

    /**
     * Tests addNamedRoute functionality
     */
    public function testAddNamedRoute()
    {
        $wrappedGenerator = new ConcreteGenerator();
        $generator = new HandlerWrapperGenerator($wrappedGenerator);

        $generator->addNamedRoute('test', 'GET', '/', 'handler0');

        $data = $generator->getData();

        $this->assertCount(1, $data);
        $this->assertEquals(array('GET', '/', array('handler' => 'handler0', 'name' => 'test')), $data[0]);
    }
}

class ConcreteGenerator implements DataGenerator
{
    private $routes = array();

    public function addRoute($httpMethod, $routeData, $handler)
    {
        $this->routes[] = func_get_args();
    }

    public function getData()
    {
        return $this->routes;
    }
}
