<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\UrlGenerator;

use Nice\Router\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class SimpleUrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var DataGeneratorInterface
     */
    private $dataGenerator;
    
    private $initialized = false;
    
    private $routes = array();

    /**
     * @var Request
     */
    private $request;

    public function __construct(DataGeneratorInterface $dataGenerator)
    {
        $this->dataGenerator = $dataGenerator;
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
        
        $path = $this->routes[$name];
        if (is_array($path)) {
            $params = $path['params'];
            $path = $path['path'];
            
            foreach ($params as $param) {
                if (!isset($parameters[$param])) {
                    throw new \RuntimeException('Missing required parameter "' . $param . '". Optional parameters not currently supported');
                }
                
                $path = str_replace('{' . $param . '}', $parameters[$param], $path);
            }
        }
        
        return ($this->request ? $this->request->getBaseUrl() : '') . $path;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Initialize the generator
     */
    private function initialize()
    {
        $this->routes = $this->dataGenerator->getData();
        $this->initialized = true;
    }
}