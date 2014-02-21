nice
====

A nice PHP microframework.

This microframework integrates nikic's [FastRoute](https://github.com/nikic/FastRoute) router with the [Symfony 2 HttpKernel](https://github.com/symfony/http-kernel).

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TylerSommer\Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application(function (FastRoute\RouteCollector $r) {
       $r->addRoute('GET', '/', function (Request $request) {
               return new Response('Hello, world');
           });
   });
$app->run();
```

Further improvements to come:

* Some kind of dependency injection solution
* Production/development environments
* Caching of some kind


Installation
------------

The recommended way to install Nice is through [Composer](http://getcomposer.org/). Just create a
``composer.json`` file in your project directory and run the ``php composer.phar require`` command to
install it:

```bash
php composer.phar require tyler-sommer/nice:dev-master nikic/fast-route:dev-master
```


Usage
-----

In your `web` directory, create `index.php` and add:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TylerSommer\Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

// Enable Symfony debug error handlers
Symfony\Component\Debug\Debug::enable();

// Configure your RouteFactory to create any routes and controllers
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
```

Visit `index.php` in your browser and you should be greeted with "Hello, world".

Visit `index.php/hello/Tyler` and you will be greeted with "Hello, Tyler".
