A nice PHP microframework
=========================

[![Build Status](https://travis-ci.org/tyler-sommer/nice.png?branch=master)](https://travis-ci.org/tyler-sommer/nice)

Nice is a simple microframework for PHP 5.4 and later. Nice integrates nikic's 
[FastRoute](https://github.com/nikic/FastRoute) router with 
the [Symfony2 HttpKernel](https://github.com/symfony/HttpKernel) and 
[Dependency Injection](https://github.com/symfony/DependencyInjection) components.

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nice\Application;

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
[x] Symfony2 dependency injection
[x] Twig integration
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
use Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

// Enable Symfony debug error handlers
Symfony\Component\Debug\Debug::enable();

$app = new Application();

// Configure your routes
$app->set('routes', function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function (Application $app, Request $request) {
            return new Response('Hello, world');
        });

    $r->addRoute('GET', '/hello/{name}', function (Application $app, Request $request, $name) {
            return new Response('Hello, ' . $name . '!');
        });
});

// Run the application
$app->run();
```

Visit `index.php` in your browser and you'll see the message "Hello, world".

Visit `index.php/hello/Tyler` and you will see "Hello, Tyler".


Use with [Twig](http://twig.sensiolabs.org)
-------------------------------------------

Add Twig to your project:

```bash
php composer.phar require twig/twig:dev-master
```

Then, in your front controller:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();

// Set your templates directory
$app->setParameter('twig.template_dir', __DIR__ . '/../views');

$app->set('routes', function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/hello/{name}', function (Application $app, Request $request, $name) {
            // Use the Twig service to render templates
            $rendered = $app->get('twig')->render('index.html.twig', array(
                    'name' => $name
                ));
            
            return new Response($rendered);
        });
});

// Run the application
$app->run();
```


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
use Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

// Create your app
$app = new Application($routeFactory);

// ..add your routes
$app->set('routes', function (FastRoute\RouteCollector $r) {
    // ...
});

// ..and then create the stack
$stack = new Stack\Builder();
$stack->push(function ($app) {
        return new HttpCache($app, new Store(__DIR__.'/cache'));
    });

$app = $stack->resolve($app);

// Run the app
Stack\run($app);
```
