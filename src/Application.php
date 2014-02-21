<?php

namespace TylerSommer\Nice;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use TylerSommer\Nice\Router\RouterSubscriber;

class Application extends HttpKernel
{
    public function __construct(
        callable $routeFactory,
        EventDispatcherInterface $dispatcher = null, 
        ControllerResolverInterface $resolver = null, 
        RequestStack $requestStack = null
    ) {
        $dispatcher = $dispatcher ?: new EventDispatcher();
        $resolver   = $resolver ?: new ControllerResolver();
        
        parent::__construct($dispatcher, $resolver, $requestStack);
        
        $routeDispatcher = \FastRoute\simpleDispatcher($routeFactory);
        $subscriber = new RouterSubscriber($routeDispatcher);
        
        $dispatcher->addSubscriber($subscriber);
    }
}