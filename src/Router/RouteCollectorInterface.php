<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router;

/**
 * Defines the contract any RouteCollector must implement
 *
 * This interface is extracted from FastRoute\RouteCollector
 */
interface RouteCollectorInterface
{
    /**
     * Returns the collected route data
     *
     * @return array
     */
    public function getData();
}
