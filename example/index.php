<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TylerSommer\Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

// Enable Symfony debug error handlers
Symfony\Component\Debug\Debug::enable();

$app = new Application();

// Configure your routes
$app->set('routes', function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/', function (Request $request) {
                return new Response('Hello, world');
            });

        $r->addRoute('GET', '/hello/{name}', function (Request $request, $name) {
                return new Response('Hello, ' . $name . '!');
            });
    });

// Run the application
$app->run();