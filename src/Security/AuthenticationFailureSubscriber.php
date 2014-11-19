<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Security;

use Nice\Security\Event\SecurityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds a session variable to indicate failed login attempt
 */
class AuthenticationFailureSubscriber implements EventSubscriberInterface
{
    const AUTHENTICATION_ERROR = '__nice.authentication_error';

    /**
     * @param SecurityEvent $event
     */
    public function onLoginSuccess(SecurityEvent $event)
    {
        $request = $event->getRequest();
        $request->getSession()->remove(self::AUTHENTICATION_ERROR);
    }

    /**
     * @param SecurityEvent $event
     */
    public function onLoginFail(SecurityEvent $event)
    {
        $request = $event->getRequest();
        $request->getSession()->set(self::AUTHENTICATION_ERROR, true);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::LOGIN_SUCCESS => array('onLoginSuccess'),
            Events::LOGIN_FAIL    => array('onLoginFail'),
        );
    }
}
