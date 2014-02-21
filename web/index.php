<?php

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

// Create a Request from Globals
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

// Set up the HttpKernel
$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
$resolver = new \Symfony\Component\HttpKernel\Controller\ControllerResolver();
$kernel = new \Symfony\Component\HttpKernel\HttpKernel($dispatcher, $resolver);

// Configure your Routes
$routeDispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/', 'handler1');
        $r->addRoute('GET', '/test', 'handler2');
    });

$dispatcher->addSubscriber(new \TylerSommer\Nice\Router\RouterSubscriber($routeDispatcher));

// Controllers
function handler1(\Symfony\Component\HttpFoundation\Request $request) {
    return new \Symfony\Component\HttpFoundation\Response('handler 1 got fired');
}

function handler2() {
    return new \Symfony\Component\HttpFoundation\Response('handler 2 got fired');
}

// Handle the Request and send a Response
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);