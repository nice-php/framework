<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router;

/**
 * Defines the contract any RouteMapper must implement
 */
interface RouteMapperInterface
{
    /**
     * Map a handler to the given methods and route
     *
     * @param string          $route    The route to match against
     * @param string          $name     The name of the route
     * @param string|callable $handler  The handler for the route
     * @param array|string[]  $methods  The HTTP methods for this handler
     * @return void
     */
    public function map($route, $name, $handler, array $methods = array('GET'));
}
