<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Session;

use PHPUnit\Framework\TestCase;
use Nice\Session\SessionSubscriber;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionSubscriberTest extends TestCase
{
    /**
     * Test a Master request
     */
    public function testMasterRequestInjectsSession()
    {
        $session = new Session(new MockFileSessionStorage());
        $container = new Container();
        $container->set('session', $session);
        $subscriber = new SessionSubscriber($container);
        $request = Request::create('/', 'GET');

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->assertSame($session, $request->getSession());
    }

    /**
     * Test a Sub request
     */
    public function testSubRequestDoesNotInjectSession()
    {
        $session = new Session(new MockFileSessionStorage());
        $container = new Container();
        $container->set('session', $session);
        $subscriber = new SessionSubscriber($container);
        $request = Request::create('/', 'GET');

        $event = $this->getEvent($request, HttpKernelInterface::SUB_REQUEST);

        $subscriber->onKernelRequest($event);

        $this->expectException('Symfony\Component\HttpFoundation\Exception\SessionNotFoundException');
        $this->expectExceptionMessage('Session has not been set.');

        $request->getSession();
    }

    /**
     * Test a Container with no Session
     */
    public function testNoSessionDoesNotInject()
    {
        $container = new Container();
        $subscriber = new SessionSubscriber($container);
        $request = Request::create('/', 'GET');

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->expectException('Symfony\Component\HttpFoundation\Exception\SessionNotFoundException');
        $this->expectExceptionMessage('Session has not been set.');

        $request->getSession();
    }

    /**
     * Test that the subscriber will not replace an existing Session on a Request
     */
    public function testDoesNotReplaceExistingSession()
    {
        $container = new Container();
        $container->set('session', new Session(new MockFileSessionStorage()));
        $subscriber = new SessionSubscriber($container);

        $session = new Session(new MockFileSessionStorage());
        $request = Request::create('/', 'GET');
        $request->setSession($session);

        $event = $this->getEvent($request);

        $subscriber->onKernelRequest($event);

        $this->assertSame($session, $request->getSession());
    }

    /**
     * Tests the static getSubscribedEvents method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
                KernelEvents::REQUEST => array('onKernelRequest', 128),
            ), SessionSubscriber::getSubscribedEvents());
    }

    /**
     * @param Request $request
     *
     * @return RequestEvent
     */
    private function getEvent(Request $request, $type = HttpKernelInterface::MASTER_REQUEST)
    {
        return new RequestEvent(
            $this->getMockForAbstractClass('Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            $type
        );
    }
}
