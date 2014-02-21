<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TylerSommer\Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

// Configure your RouteFactory
$routeFactory = function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function (Request $request) {
            return new Response('Hello, world');
        });

    $r->addRoute('GET', '/hello/{name}', function (Request $request, $name) {
            return new Response('Hello, ' . $name . '!');
        });
};

// Handle the Request and send a Response
$app = new Application($routeFactory);
$app->run();
