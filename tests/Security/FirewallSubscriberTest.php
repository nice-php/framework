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
        $request = Request::create('/');
        
        $susbcriber = $this->getSubscriber();
        $event = $this->getEvent($request);

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
    private function getSubscriber()
    {
        $loginMatcher  = new RequestMatcher('^/login');
        $authMatcher   = new RequestMatcher('^/login', null, 'POST');
        $logoutMatcher = new RequestMatcher('^/logout');
        $authenticator = new SimpleAuthenticator('user', 'pass');
        
        return new FirewallSubscriber(
            $loginMatcher,
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
