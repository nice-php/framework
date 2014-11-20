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
     * Map a handler to a GET method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     * @return void
     */
    public function get($route, $name, $handler);

    /**
     * Map a handler to a POST method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     * @return void
     */
    public function post($route, $name, $handler);

    /**
     * Map a handler to a HEAD method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     * @return void
     */
    public function head($route, $name, $handler);

    /**
     * Map a handler to a PUT method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     * @return void
     */
    public function put($route, $name, $handler);

    /**
     * Map a handler to a DELETE method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     * @return void
     */
    public function delete($route, $name, $handler);

    /**
     * Map a handler to a PATCH method route
     *
     * @param string          $route
     * @param string          $name
     * @param string|callable $handler
     * @return void
     */
    public function patch($route, $name, $handler);
}
