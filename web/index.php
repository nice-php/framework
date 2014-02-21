<?php

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
$resolver = new \Symfony\Component\HttpKernel\Controller\ControllerResolver();
$kernel = new \Symfony\Component\HttpKernel\HttpKernel($dispatcher, $resolver);

$routeDispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/', 'handler1');
        $r->addRoute('GET', '/test', 'handler2');
    });

$dispatcher->addSubscriber(new \TylerSommer\Nice\Router\RouterSubscriber($routeDispatcher));

function handler1(\Symfony\Component\HttpFoundation\Request $request) {
    return new \Symfony\Component\HttpFoundation\Response('handler 1 got fired');
}

function handler2() {
    return new \Symfony\Component\HttpFoundation\Response('handler 2 got fired');
}

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);