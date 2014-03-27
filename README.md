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
use Nice\Router\RouteCollector;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app->set('routes', function (RouteCollector $r) {
    $r->addRoute('GET', '/', function (Request $request) {
            return new Response('Hello, world');
        });
});
$app->run();
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

In your project root, create three directories: `cache`, `logs`, `web`. 

> `cache` and `logs` must be writable by your webserver

In your `web` directory, create `index.php` and add:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nice\Application;
use Nice\Router\RouteCollector;

require __DIR__ . '/../vendor/autoload.php';

// Enable Symfony debug error handlers
Symfony\Component\Debug\Debug::enable();

$app = new Application();

// Configure your routes
$app->set('routes', function (RouteCollector $r) {
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

Visit `index.php/hello/Tyler` and you will see "Hello, Tyler!".


#### A word about caching

Passing `false` as the third parameter of `Nice\Application` constructor will disable
caching. The cache directory will be null if caching is disabled, which can be checked
in your own code.

```php
<?php

use Nice\Application;

// ...

$app = new Application('prod', true, false);

// Caching is disabled; the Cache Directory is null.
assert($app->getCacheDir() === null);

// or by calling isCacheEnabled
assert($app->isCacheEnabled() === false);
```


#### Enabling session management

By default, session management is disabled. If you'd like to enable it, add the following:

```php
<?php

use Nice\Application;
use Nice\Extension\SessionExtension;

// ...

$app = new Application();
$app->registerExtension(new SessionExtension());

// ...

$app->run();
```


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
use Nice\Router\RouteCollector;
use Nice\Extension\TwigExtension;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();

// Register the Twig extension
$app->registerExtension(new TwigExtension(__DIR__ . '/../views'));

$app->set('routes', function (RouteCollector $r) {
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
use Nice\Router\RouteCollector;

require __DIR__ . '/../vendor/autoload.php';

// Create your app
$app = new Application();

// ..add your routes
$app->set('routes', function (RouteCollector $r) {
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


Advanced usage
--------------

You can subclass `Nice\Application` to accomplish more complex setups. Override 
the `registerContainerConfiguration` and `registerDefaultExtension` methods to
allow for extended customization.

This behavior is borrowed from Symfony's `KernelInterface`, allowing
for annotations, yaml, xml, php, and closures to provide the configuration.

An example is coming soon. 
