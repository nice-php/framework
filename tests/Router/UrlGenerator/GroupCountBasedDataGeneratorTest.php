<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\UrlGenerator;

use PHPUnit\Framework\TestCase;
use Nice\Router\UrlGenerator\GroupCountBasedDataGenerator;

class GroupCountBasedDataGeneratorTest extends TestCase
{
    /**
     * Test basic functionality
     */
    public function testFunctionality()
    {
        $collector = $this->getMockForAbstractClass('Nice\Router\RouteCollectorInterface');
        $collector->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array(
                // Static routes
                array(
                    'GET' => array(
                        '/' => array(
                            'name' => 'home',
                            'controller' => 'handler1',
                        ),
                    ),
                ),

                // Dynamic routes
                array(
                    'GET' => array(
                        array(
                            'regex' => '~^(?|/user/([^/]+)/show)$~',
                            'routeMap' => array(
                                2 => array(
                                    array(
                                        'name' => 'user_show',
                                        'handler' => 'handler2',
                                    ),
                                    array(
                                        'id' => 'id',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )));

        $generator = new GroupCountBasedDataGenerator($collector);
        $data = $generator->getData();

        $this->assertEquals(array(
            'home' => '/',
            'user_show' => array(
                'path' => '/user/{id}/show',
                'params' => array(
                    'id' => 'id',
                ),
            ),
        ), $data);
    }

    /**
     * Test invalid data handling
     */
    public function testInvalidData()
    {
        $collector = $this->getMockForAbstractClass('Nice\Router\RouteCollectorInterface');
        $collector->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array(
                array(),

                array(
                    'GET' => array(
                        array(
                            'regex' => '~^(?|/user/([^/]+)/show|/user/([^/]+)/edit)$~',
                            'routeMap' => array(
                                // Invalid index
                                0 => array(
                                    array(
                                        'name' => 'user_show',
                                        'handler' => 'handler2',
                                    ),
                                    array(
                                        'id' => 'id',
                                    ),
                                ),
                                // Valid, but No "name" attribute
                                3 => array(
                                    array(
                                        'handler' => 'handler2',
                                    ),
                                    array(
                                        'id' => 'id',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )));

        $generator = new GroupCountBasedDataGenerator($collector);
        $data = $generator->getData();

        $this->assertEquals(array(), $data);
    }
}
