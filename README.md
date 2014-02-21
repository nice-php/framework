nice
====

A nice PHP microframework.

This microframework integrates nikic's [FastRoute](https://github.com/nikic/FastRoute) router with the [Symfony 2 HttpKernel](https://github.com/symfony/http-kernel).

Further improvements to come:

* Some kind of dependency injection solution
* Production/development environments
* Caching of some kind


Installation
------------

Ensure you have [composer installed](http://getcomposer.org/).

Then, use `composer create-project` to get started:

```bash
composer create-project tyler-sommer/nice my-project
```

Once Composer finishes setting up the project, visit `index.php` in your browser.

See `index.php` for an example of defining routes and controllers.