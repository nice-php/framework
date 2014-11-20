<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;

/**
 * A base class for any RouteCollector
 *
 * This class is based on FastRoute\RouteCollector
 */
abstract class RouteCollector implements RouteCollectorInterface, RouteMapperInterface
{
    /**
     * @var RouteParser
     */
    private $routeParser;

    /**
     * @var DataGenerator
     */
    private $dataGenerator;

    /**
     * @var bool
     */
    private $collected = false;

    /**
     * Constructor
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->routeParser   = $routeParser;
        $this->dataGenerator = $dataGenerator;
    }

    /**
     * Adds a route to the collection
     *
     * @param string $httpMethod
     * @param string $route
     * @param mixed  $handler
     */
    public function addRoute($httpMethod, $route, $handler)
    {
        $routeData = $this->routeParser->parse($route);

        $this->dataGenerator->addRoute($httpMethod, $routeData, $handler);
    }

    /**
     * Adds a named route to the collection
     *
     * @param string $name
     * @param string $httpMethod
     * @param string $route
     * @param mixed  $handler
     *
     * @throws \RuntimeException
     */
    public function addNamedRoute($name, $httpMethod, $route, $handler)
    {
        $routeData = $this->routeParser->parse($route);

        if ($this->dataGenerator instanceof NamedDataGeneratorInterface) {
            $this->dataGenerator->addNamedRoute($name, $httpMethod, $routeData, $handler);
        } else {
            throw new \RuntimeException('The injected generator does not support named routes');
        }
    }

    /**
     * Returns the collected route data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->collected) {
            $this->collectRoutes();

            $this->collected = true;
        }

        return $this->dataGenerator->getData();
    }

    /**
     * Map a handler to a GET method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     */
    public function get($route, $name, $handler)
    {
        $this->addNamedRoute($name, 'GET', $route, $handler);
    }

    /**
     * Map a handler to a POST method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     */
    public function post($route, $name, $handler)
    {
        $this->addNamedRoute($name, 'POST', $route, $handler);
    }

    /**
     * Map a handler to a HEAD method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     */
    public function head($route, $name, $handler)
    {
        $this->addNamedRoute($name, 'HEAD', $route, $handler);
    }

    /**
     * Map a handler to a PUT method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     */
    public function put($route, $name, $handler)
    {
        $this->addNamedRoute($name, 'PUT', $route, $handler);
    }

    /**
     * Map a handler to a DELETE method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     */
    public function delete($route, $name, $handler)
    {
        $this->addNamedRoute($name, 'DELETE', $route, $handler);
    }

    /**
     * Map a handler to a PATCH method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     */
    public function patch($route, $name, $handler)
    {
        $this->addNamedRoute($name, 'PATCH', $route, $handler);
    }

    /**
     * Perform any collection
     *
     * @return void
     */
    abstract protected function collectRoutes();
}
