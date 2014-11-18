<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\RouteCollector;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Nice\Router\RouteCollector;

/**
 * A simple RouteCollector implementation
 *
 * This class is based on FastRoute\RouteCollector
 */
class SimpleCollector extends RouteCollector
{
    /**
     * @var callable
     */
    private $routeFactory;

    /**
     * Constructor
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     * @param callable      $routeFactory
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator, callable $routeFactory)
    {
        parent::__construct($routeParser, $dataGenerator);

        $this->routeFactory = $routeFactory;
    }

    /**
     * Calls the RouteFactory, passing this collector as the first argument
     *
     * @return void
     */
    protected function collectRoutes()
    {
        call_user_func($this->routeFactory, $this);
    }
}
