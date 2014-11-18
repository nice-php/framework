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
abstract class RouteCollector implements RouteCollectorInterface
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
     * Perform any collection
     *
     * @return void
     */
    abstract protected function collectRoutes();
}
