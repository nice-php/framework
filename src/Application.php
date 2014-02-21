<?php

namespace TylerSommer\Nice;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use TylerSommer\Nice\Router\RouterSubscriber;

class Application extends HttpKernel
{
    /**
     * Constructor
     * 
     * @param callable                    $routeFactory
     * @param EventDispatcherInterface    $dispatcher
     * @param ControllerResolverInterface $resolver
     * @param RequestStack                $requestStack
     */
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

    /**
     * Helper method to get things going.
     * 
     * Inspired by Silex
     */
    public function run()
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }
}
