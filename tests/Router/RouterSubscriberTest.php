<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Nice\Router\RouterSubscriber;
use Symfony\Component\HttpKernel\KernelEvents;

class RouterSubscriberTest extends TestCase
{
    /**
     * Test a Found route
     */
    public function testFound()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/', 'GET');

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->assertEquals('handler1', $request->get('_controller'));
    }

    /**
     * Test a Found route with parameters
     */
    public function testFoundWithParams()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/hello/test', 'GET');

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->assertEquals('handler2', $request->get('_controller'));
        $this->assertEquals('test', $request->get('value'));
    }

    /**
     * Test a match, but Method Not Allowed route
     */
    public function testMethodNotAllowed()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/', 'POST');

        $event = $this->getEvent($request);

        $this->expectException('Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException');

        $subscriber->onKernelRequest($event);
    }

    /**
     * Test a Request that already has a _controller
     */
    public function testRequestAlreadyResolved()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/', 'POST');
        $request->attributes->set('_controller', 'handler0');

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->assertEquals('handler0', $request->attributes->get('_controller'));
    }

    /**
     * Test not found
     */
    public function testNotFound()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/not-real', 'GET');

        $event = $this->getEvent($request);

        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $subscriber->onKernelRequest($event);
    }

    /**
     * Tests the static getSubscribedEvents method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
                KernelEvents::REQUEST => array('onKernelRequest', 0),
            ), RouterSubscriber::getSubscribedEvents());
    }

    /**
     * @return RouterSubscriber
     */
    private function getSubscriber()
    {
        $routeDispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
                $r->addRoute('GET', '/', 'handler1');
                $r->addRoute('GET', '/hello/{value}', 'handler2');
            });

        $subscriber = new RouterSubscriber($routeDispatcher);

        return $subscriber;
    }

    /**
     * @param Request $request
     *
     * @return RequestEvent
     */
    private function getEvent(Request $request)
    {
        return new RequestEvent(
            $this->getMockForAbstractClass('Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }
}
