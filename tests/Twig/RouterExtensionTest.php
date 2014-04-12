<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Twig;

use Nice\Twig\RouterExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;

class RouterExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests getCurrentController
     */
    public function testCurrentController()
    {
        $extension = $this->getExtension();
        
        $this->assertEquals('Home', $extension->getController());
    }

    /**
     * Tests getCurrentAction
     */
    public function testCurrentAction()
    {
        $extension = $this->getExtension();

        $this->assertEquals('index', $extension->getAction());
    }

    /**
     * Tests getCurrentRoute
     */
    public function testCurrentRoute()
    {
        $extension = $this->getExtension();

        $this->assertEquals('home', $extension->getRoute());
    }

    /**
     * Tests isCurrentController
     */
    public function testIsCurrentController()
    {
        $extension = $this->getExtension();
        
        $this->assertTrue($extension->isCurrentController(array('Home', 'Dashboard')));
        $this->assertFalse($extension->isCurrentController(array('Dashboard', 'SomeOther')));
        $this->assertTrue($extension->isCurrentController('Home'));
    }

    /**
     * Tests isCurrentRoute
     */
    public function testIsCurrentRoute()
    {
        $extension = $this->getExtension();

        $this->assertTrue($extension->isCurrentRoute('home'));
        $this->assertFalse($extension->isCurrentRoute('dashboard'));
    }

    /**
     * Miscellaneous tests
     */
    public function testBasicMethods()
    {
        $extension = $this->getExtension('/');
        $functions = $extension->getFunctions();
        $globals   = $extension->getGlobals();

        $this->assertCount(5, $functions);
        $this->assertTrue(isset($functions['current_controller']));
        $this->assertTrue(isset($functions['current_action']));
        $this->assertTrue(isset($functions['current_route']));
        $this->assertTrue(isset($functions['is_current_controller']));
        $this->assertTrue(isset($functions['is_current_route']));

        $this->assertCount(1, $globals);
        $this->assertTrue(isset($globals['app']));

        $this->assertEquals('router', $extension->getName());
    }

    /**
     * Tests that an exception is thrown when the request service is unavailable
     */
    public function testCannotGetRequest()
    {
        $container = new Container();
        $extension = new RouterExtension($container);
        
        $this->setExpectedException('RuntimeException', 'Unable to get "request" service');
        
        $extension->getController();
    }

    /**
     * @param string $uri The URI to give to the request
     *
     * @return RouterExtension
     */
    public function getExtension($uri = '/', $controller = 'HomeController::indexAction', $route = 'home')
    {
        $request = Request::create($uri);
        $request->attributes->set('_controller', $controller);
        $request->attributes->set('_route', $route);
        $container = new Container();
        $container->set('request', $request);
        $container->set('app', new \stdClass());
        $container->addScope(new Scope('request'));
        $container->enterScope('request');

        return new RouterExtension($container);
    }
}
