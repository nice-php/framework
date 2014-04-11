<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\UrlGenerator;

use Nice\Router\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SimpleUrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var DataSourceInterface
     */
    private $dataSource;
    
    private $initialized = false;
    
    private $routes = array();

    /**
     * @var Request
     */
    private $request;

    public function __construct(DataSourceInterface $dataSource, Request $request)
    {
        $this->dataSource = $dataSource;
        $this->request = $request;
    }
    
    /**
     * Generate a URL for the given route
     *
     * @param string $name       The name of the route to generate a url for
     * @param array  $parameters Parameters to pass to the route
     * @param bool   $absolute   If true, the generated route should be absolute
     *
     * @return string
     */
    public function generate($name, array $parameters = array(), $absolute = false)
    {
        if (!$this->initialized) {
            $this->initialize();
        }
        
        return $this->request->getBaseUrl() . $this->routes[$name];
    }

    /**
     * Initialize the generator
     */
    private function initialize()
    {
        $this->routes = $this->dataSource->getData();
        $this->initialized = true;
    }
}