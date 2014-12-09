<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\UrlGenerator;

use Nice\Router\UrlGenerator\SimpleUrlGenerator;
use Symfony\Component\HttpFoundation\Request;

class SimpleUrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic functionality
     */
    public function testFunctionality()
    {
        $generator = $this->getGenerator();

        $this->assertEquals('/', $generator->generate('home'));
    }

    /**
     * Test base URL functionality
     */
    public function testBaseUrl()
    {
        $generator = $this->getGenerator();

        $request = Request::create('https://www.example.com/subdirectory/somepage', 'GET', array(), array(), array(), array(
            'SCRIPT_FILENAME' => 'index.php',
            'PHP_SELF' => '/subdirectory/index.php'
        ));
        $generator->setRequest($request);

        $this->assertEquals('/subdirectory/', $generator->generate('home'));
    }

    /**
     * Test absolute URL functionality
     */
    public function testAbsoluteUrl()
    {
        $generator = $this->getGenerator();

        $request = Request::create('https://www.example.com/subdirectory/somepage', 'GET', array(), array(), array(), array(
            'SCRIPT_FILENAME' => 'index.php',
            'PHP_SELF' => '/subdirectory/index.php'
        ));
        $generator->setRequest($request);

        $this->assertEquals('https://www.example.com/subdirectory/', $generator->generate('home', array(), true));
    }

    /**
     * Test a dynamic route
     */
    public function testDynamicRoute()
    {
        $generator = $this->getGenerator(array(
            'user_edit' => array(
                'params' => array(
                    'id'
                ),
                'path' => '/user/{id}/edit'
            )
        ));

        $this->assertEquals('/user/123/edit', $generator->generate('user_edit', array('id' => 123)));
    }

    /**
     * Test a dynamic route with a missing parameter
     */
    public function testDynamicRouteWithMissingParameter()
    {
        $generator = $this->getGenerator(array(
            'user_edit' => array(
                'params' => array(
                    'id'
                ),
                'path' => '/user/{id}/edit'
            )
        ));

        $this->setExpectedException('RuntimeException', 'Missing required parameter');

        $this->assertEquals('/user/123/edit', $generator->generate('user_edit'));
    }

    private function getGenerator(array $routes = array('home' => '/'))
    {
        $dataGenerator = $this->getMockForAbstractClass('Nice\Router\UrlGenerator\DataGeneratorInterface');
        $dataGenerator->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($routes));

        return new SimpleUrlGenerator($dataGenerator);
    }
}
