Nice PHP microframework
=========================

[![Build Status](http://img.shields.io/travis/nice-php/framework.svg)](https://travis-ci.org/nice-php/framework)
[![Coverage](http://img.shields.io/codeclimate/coverage/nice-php/framework.svg)](https://codeclimate.com/github/nice-php/framework)
[![Code Climate](http://img.shields.io/codeclimate/coverage-letter/nice-php/framework.svg)](https://codeclimate.com/github/nice-php/framework)
[![Latest Release](http://img.shields.io/packagist/v/nice/framework.svg)](https://packagist.org/packages/nice/framework)

Nice is a simple microframework for PHP 5.6 and later. Nice integrates nikic's 
[FastRoute](https://github.com/nikic/FastRoute) router with 
the [Symfony2 HttpKernel](https://github.com/symfony/HttpKernel) and 
[Dependency Injection](https://github.com/symfony/DependencyInjection) components.

Nice comes with built-in [session management](http://docs.niceframework.com/nice/latest/extensions/sessions),
[simple authentication](http://docs.niceframework.com/nice/latest/extensions/security), and logging utilizing
[Monolog](http://docs.niceframework.com/nice/latest/extensions/log).
[Twig](http://docs.niceframework.com/nice/latest/extensions/twig), along with
[Doctrine DBAL](http://docs.niceframework.com/nice/latest/extensions/doctrine-dbal) and
[ORM](http://docs.niceframework.com/nice/latest/extensions/doctrine-orm) integration is
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
    $r->map('/', 'home', function (Request $request) {
        return new Response('Hello, world');
    });
});
$app->run();
```


Documentation
-------------

View [the online documentation](http://docs.niceframework.com), or the check out the
[markdown documentation source code](https://github.com/nice-php/docs).
