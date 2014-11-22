A nice PHP microframework
=========================

[![Build Status](http://img.shields.io/travis/nice-php/framework.svg)](https://travis-ci.org/nice-php/framework)
[![Coverage](http://img.shields.io/codeclimate/coverage/github/nice-php/framework.svg)](https://codeclimate.com/github/nice-php/framework)
[![Code Climate](http://img.shields.io/codeclimate/github/nice-php/framework.svg)](https://codeclimate.com/github/nice-php/framework)
[![Latest Release](http://img.shields.io/packagist/v/nice/framework.svg)](https://packagist.org/packages/nice/framework)

Nice is a simple microframework for PHP 5.4 and later. Nice integrates nikic's 
[FastRoute](https://github.com/nikic/FastRoute) router with 
the [Symfony2 HttpKernel](https://github.com/symfony/HttpKernel) and 
[Dependency Injection](https://github.com/symfony/DependencyInjection) components.

Nice comes with built-in [session management](https://github.com/nice-php/docs/blob/master/extensions/sessions.md),
[simple authentication](https://github.com/nice-php/docs/blob/master/extensions/security.md), and logging utilizing
[Monolog](https://github.com/nice-php/docs/blob/master/extensions/log.md).
[Twig](https://github.com/nice-php/docs/blob/master/extensions/twig.md), and
[Doctrine DBAL](https://github.com/nice-php/docs/blob/master/extensions/doctrine-dbal.md) integration is
also available.

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

View [the online documentation](http://niceframework.com), or the check out the
[markdown documentation source code](https://github.com/nice-php/docs).
