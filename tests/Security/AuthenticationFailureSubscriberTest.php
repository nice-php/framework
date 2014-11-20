<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Security;

use Nice\Security\AuthenticationFailureSubscriber;
use Nice\Security\Event\SecurityEvent;
use Nice\Security\Events;
use Nice\Tests\Helpers\MockRequestFactoryTrait;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationFailureSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use MockRequestFactoryTrait;

    /**
     * Test the listener after a failed login
     */
    public function testLoginFailure()
    {
        $request = $this->createRequest('/');
        $event = $this->getEvent($request);

        $subscriber = new AuthenticationFailureSubscriber();
        $subscriber->onLoginFail($event);

        $this->assertTrue($request->getSession()->has(AuthenticationFailureSubscriber::AUTHENTICATION_ERROR));

        return $request;
    }

    /**
     * Test the listener after a successful login
     *
     * @depends testLoginFailure
     */
    public function testLoginSuccess(Request $request)
    {
        $subscriber = new AuthenticationFailureSubscriber();

        $this->assertTrue($request->getSession()->has(AuthenticationFailureSubscriber::AUTHENTICATION_ERROR));

        $event = $this->getEvent($request);
        $subscriber->onLoginSuccess($event);

        $this->assertFalse($request->getSession()->has(AuthenticationFailureSubscriber::AUTHENTICATION_ERROR));
    }

    /**
     * Tests the static getSubscribedEvents method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
            Events::LOGIN_SUCCESS => array('onLoginSuccess'),
            Events::LOGIN_FAIL    => array('onLoginFail'),
        ), AuthenticationFailureSubscriber::getSubscribedEvents());
    }

    /**
     * @param Request $request
     *
     * @return SecurityEvent
     */
    private function getEvent(Request $request)
    {
        return new SecurityEvent($request);
    }
}
