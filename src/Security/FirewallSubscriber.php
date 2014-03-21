<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles Authentication
 */
class FirewallSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface
     */
    private $firewallMatcher;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface
     */
    private $authMatcher;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $loginPath;

    /**
     * @var string
     */
    private $successPath;

    /**
     * @var string
     */
    private $tokenKey;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface
     */
    private $logoutMatcher;

    /**
     * Constructor
     * 
     * @param RequestMatcherInterface $firewallMatcher
     * @param RequestMatcherInterface $authMatcher
     * @param string                  $username
     * @param string                  $password
     * @param string                  $loginPath
     * @param string                  $successPath
     * @param string                  $tokenKey
     */
    public function __construct(
        RequestMatcherInterface $firewallMatcher,
        RequestMatcherInterface $authMatcher,
        RequestMatcherInterface $logoutMatcher,
        $username, 
        $password, 
        $loginPath, 
        $successPath,
        $tokenKey
    ) {
        $this->firewallMatcher = $firewallMatcher;
        $this->authMatcher = $authMatcher;
        $this->logoutMatcher = $logoutMatcher;
        $this->username = $username;
        $this->password = $password;
        $this->loginPath = $loginPath;
        $this->successPath = $successPath;
        $this->tokenKey = $tokenKey;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if ($this->authMatcher->matches($request)) {
            $this->handleAuthentication($event);
            
            return;
        }

        if (! ($session = $request->getSession())) {
            $event->setResponse(new Response('', 403));

            return;
        }

        if ($this->logoutMatcher->matches($request)) {
            $this->handleLogout($event);

            return;
        }
        
        if (!$this->firewallMatcher->matches($request)) {
            return;
        }
        
        if (!$session->has($this->tokenKey)) {
            $this->redirectForAuthentication($event);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 8),
        );
    }

    private function handleAuthentication(GetResponseEvent $event) 
    { 
        $request = $event->getRequest();
        
        if ($request->get('username') === $this->username
            && $request->get('password') === $this->password
        ) {
            $session = $request->getSession();
            $session->set($this->tokenKey, true);
            
            $event->setResponse(new RedirectResponse($event->getRequest()->getBaseUrl() . $this->successPath));
            
        } else {
            $event->setResponse(new RedirectResponse($event->getRequest()->getBaseUrl() . $this->loginPath));
        }
    }
    
    private function redirectForAuthentication(GetResponseEvent $event)
    {
        $event->setResponse(new RedirectResponse($event->getRequest()->getBaseUrl() . $this->loginPath));
    }

    private function handleLogout(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $session->remove($this->tokenKey);
        
        $event->setResponse(new RedirectResponse($event->getRequest()->getBaseUrl() . $this->loginPath));
    }
}
