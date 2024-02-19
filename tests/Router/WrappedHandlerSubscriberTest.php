<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router;

use PHPUnit\Framework\TestCase;
use Nice\Router\WrappedHandlerSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class WrappedHandlerSubscriberTest extends TestCase
{
    /**
     * Tests basic functionality
     */
    public function testBasicFunctionality()
    {
        $subscriber = $this->getSubscriber();
        $request = Request::create('/', 'GET');
        $request->attributes->set('_controller', array('name' => 'test', 'handler' => 'handler1'));

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->assertEquals('handler1', $request->get('_controller'));
        $this->assertEquals('test', $request->get('_route'));
    }

    /**
     * Tests the static getSubscribedEvents method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
                KernelEvents::REQUEST => array('onKernelRequest', -1),
            ), WrappedHandlerSubscriber::getSubscribedEvents());
    }

    /**
     * @return WrappedHandlerSubscriber
     */
    private function getSubscriber()
    {
        $subscriber = new WrappedHandlerSubscriber();

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
