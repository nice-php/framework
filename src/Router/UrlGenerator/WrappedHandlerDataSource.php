<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\UrlGenerator;

use Nice\Router\RouteCollectorInterface;

class WrappedHandlerDataSource implements DataSourceInterface
{
    /**
     * @var \Nice\Router\RouteCollectorInterface
     */
    private $routeCollector;

    public function __construct(RouteCollectorInterface $routeCollector)
    {
        $this->routeCollector = $routeCollector;
    }
    
    /**
     * Get formatted route data for use by a URL generator
     *
     * @return array
     */
    public function getData()
    {
        $routes = $this->routeCollector->getData();
        $data = array();
        foreach ($routes[0] as $path => $methods) {
            $handler = reset($methods);
            $data[$handler['name']] = $path;
        }
        
        // TODO: Dynamic routes
        
        return $data;
    }
}