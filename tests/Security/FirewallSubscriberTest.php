<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Security;

use Nice\Security\Authenticator\SimpleAuthenticator;
use Nice\Security\FirewallSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class FirewallSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test an unmatched firewall
     */
    public function testUnmatchedFirewall()
    {
        $request = $this->getRequest('/');
        
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
        $request = $this->getRequest('/admin');

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
        $request = $this->getRequest('/admin', 'GET', false);

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
        $request = $this->getRequest('/login', 'POST');

        $susbcriber = $this->getSubscriber(false);
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('/login', $response->headers->get('Location'));
    }

    /**
     * Test that a successful login redirects to success
     */
    public function testSuccessfulLoginRedirectsToSuccess()
    {
        $request = $this->getRequest('/login', 'POST');

        $susbcriber = $this->getSubscriber(true);
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);

        $this->assertTrue($request->getSession()->get('__authed'));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('/admin', $response->headers->get('Location'));
    }

    /**
     * Test that logging in without session will create a 403 response
     */
    public function testLoginWithoutSessionCreatesForbiddenResponse()
    {
        $request = $this->getRequest('/login', 'POST', false);

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
        $request = $this->getRequest('/logout');
        $request->getSession()->set('__authed', true);

        $susbcriber = $this->getSubscriber();
        $event = $this->getEvent($request);

        $this->assertNull($event->getResponse());

        $susbcriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEmpty($request->getSession()->get('__authed'));
    }

    /**
     * Test that the listener ignores subrequests
     */
    public function testIgnoresSubRequests()
    {
        $request = $this->getRequest('/admin');

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
    private function getSubscriber($authenticate = true, $expects = null)
    {
        $firewallMatcher = new RequestMatcher('^/admin');
        $authMatcher     = new RequestMatcher('^/login', null, 'POST');
        $logoutMatcher   = new RequestMatcher('^/logout');
        $authenticator   = $this->getMockForAbstractClass('Nice\Security\AuthenticatorInterface');
        $authenticator->expects($expects ?: $this->any())->method('authenticate')
            ->will($this->returnValue((bool) $authenticate));
        
        return new FirewallSubscriber(
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
     * @param string $url
     * @param string $method
     *
     * @return Request
     */
    private function getRequest($url, $method = 'GET', $session = true)
    {
        $request = Request::create($url, $method);
        
        if ($session) {
            $request->setSession(new Session(new MockArraySessionStorage()));
        }
        
        return $request;
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
