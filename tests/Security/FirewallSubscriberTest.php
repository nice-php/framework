<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Security;

use Nice\Security\Events;
use Nice\Security\FirewallSubscriber;
use Nice\Tests\Helpers\MockRequestFactoryTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class FirewallSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use MockRequestFactoryTrait;

    /**
     * Test an unmatched firewall
     */
    public function testUnmatchedFirewall()
    {
        $request = $this->createRequest('/');

        $susbcriber = $this->getSubscriber();
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    /**
     * Test that the listener redirects when not logged in
     */
    public function testRedirectsToLogin()
    {
        $request = $this->createRequest('/admin');

        $susbcriber = $this->getSubscriber();
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('/login', $response->headers->get('Location'));
    }

    /**
     * Test that the listener redirects when not logged in
     */
    public function testRedirectToLoginWithoutSessionCreatesForbiddenResponse()
    {
        $request = $this->createRequest('/admin', 'GET', false);

        $susbcriber = $this->getSubscriber();
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test that a failed login redirects the user back to the login page
     */
    public function testFailedLoginRedirectsToLogin()
    {
        $request = $this->createRequest('/login', 'POST');

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(Events::LOGIN_FAIL, function () use (&$called) {
                $called = true;
            });
        $susbcriber = $this->getSubscriber(false, null, $dispatcher);
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertTrue($called);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('/login', $response->headers->get('Location'));
    }

    /**
     * Test that a successful login redirects to success
     */
    public function testSuccessfulLoginRedirectsToSuccess()
    {
        $request = $this->createRequest('/login', 'POST');
        $called = false;

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(Events::LOGIN_SUCCESS, function () use (&$called) {
                $called = true;
            });
        $susbcriber = $this->getSubscriber(true, null, $dispatcher);
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertTrue($called);
        $this->assertTrue($request->getSession()->get('__authed'));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('/admin', $response->headers->get('Location'));
    }

    /**
     * Test that logging in without session will create a 403 response
     */
    public function testLoginWithoutSessionCreatesForbiddenResponse()
    {
        $request = $this->createRequest('/login', 'POST', false);

        $susbcriber = $this->getSubscriber(true);
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test logging out
     */
    public function testLogout()
    {
        $request = $this->createRequest('/logout');
        $request->getSession()->set('__authed', true);

        $called = false;

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(Events::LOGOUT, function () use (&$called) {
                $called = true;
            });
        $susbcriber = $this->getSubscriber(true, null, $dispatcher);
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertTrue($called);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEmpty($request->getSession()->get('__authed'));
    }

    /**
     * Test that the listener ignores subrequests
     */
    public function testIgnoresSubRequests()
    {
        $request = $this->createRequest('/admin');

        $susbcriber = $this->getSubscriber();
        $event = $this->getEvent($request, HttpKernelInterface::SUB_REQUEST);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    /**
     * Tests the static getSubscribedEvents method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
                KernelEvents::REQUEST => array('onKernelRequest', 8),
            ), FirewallSubscriber::getSubscribedEvents());
    }

    /**
     * @return FirewallSubscriber
     */
    private function getSubscriber($authenticate = true, $expects = null, $dispatcher = null)
    {
        $dispatcher      = $dispatcher ?: new EventDispatcher();
        $firewallMatcher = new RequestMatcher('^/admin');
        $authMatcher     = new RequestMatcher('^/login', null, 'POST');
        $logoutMatcher   = new RequestMatcher('^/logout');
        $authenticator   = $this->getMockForAbstractClass('Nice\Security\AuthenticatorInterface');
        $authenticator->expects($expects ?: $this->any())->method('authenticate')
            ->will($this->returnValue((bool) $authenticate));

        return new FirewallSubscriber(
            $dispatcher,
            $firewallMatcher,
            $authMatcher,
            $logoutMatcher,
            $authenticator,
            '/login',
            '/admin',
            '__authed'
        );
    }

    /**
     * @param Request $request
     *
     * @return GetResponseEvent
     */
    private function getEvent(Request $request, $type = HttpKernelInterface::MASTER_REQUEST)
    {
        return new GetResponseEvent(
            $this->getMockForAbstractClass('Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            $type
        );
    }
}
