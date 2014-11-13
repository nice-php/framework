A nice PHP microframework
=========================

[![Build Status](https://travis-ci.org/tyler-sommer/nice.png?branch=master)](https://travis-ci.org/tyler-sommer/nice)

Nice is a simple microframework for PHP 5.4 and later. Nice integrates nikic's 
[FastRoute](https://github.com/nikic/FastRoute) router with 
the [Symfony2 HttpKernel](https://github.com/symfony/HttpKernel) and 
[Dependency Injection](https://github.com/symfony/DependencyInjection) components.

Nice comes with built-in [session management](https://github.com/tyler-sommer/nice-docs/extensions/session.md),
[simple authentication](https://github.com/tyler-sommer/nice-docs/extensions/security.md),
and [Twig integration](https://github.com/tyler-sommer/nice-docs/extensions/twig.md).

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

Documentation
-------------

Go view [the online documentation](http://niceframework.com), or the download the
[markdown source](https://github.com/tyler-sommer/nice-docs).
