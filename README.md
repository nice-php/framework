nice
====

A nice PHP microframework.

This microframework integrates nikic's [FastRoute](https://github.com/nikic/FastRoute) router with the [Symfony 2 HttpKernel](https://github.com/symfony/HttpKernel).

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TylerSommer\Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app->set('routes', function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function (Request $request) {
            return new Response('Hello, world');
        });
});
$app->run();
```

#### Improvements to come:

```
[x] Some kind of dependency injection solution
[ ] Production/development environments
[ ] Caching of some kind
```

Installation
------------

The recommended way to install Nice is through [Composer](http://getcomposer.org/). Just run the 
``php composer.phar require`` command in your project directory to install it:

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
```

Visit `index.php` in your browser and you should be greeted with "Hello, world".

Visit `index.php/hello/Tyler` and you will be greeted with "Hello, Tyler".


Use with [stack middlewares](http://stackphp.com)
-------------------------------------------------

Add your favorite middlewares to your project:

```bash
php composer.phar require stack/builder:dev-master stack/run:dev-master
```

Then, in your front controller:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use TylerSommer\Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

// Configure your RouteFactory to create any routes and controllers
$routeFactory = 

// Create your app
$app = new Application($routeFactory);

// ..add your routes
$app->set('routes', function (FastRoute\RouteCollector $r) {
    // ...
});

// ..and then create the stack
$stack = new Stack\Builder();
$stack->push(function ($app) {
        $cache = new HttpCache($app, new Store(__DIR__.'/cache'));
        return $cache;
    });

$app = $stack->resolve($app);

// Run the app
Stack\run($app);
```
