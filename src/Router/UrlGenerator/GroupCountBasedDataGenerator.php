<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\UrlGenerator;

use Nice\Router\RouteCollectorInterface;

class GroupCountBasedDataGenerator implements DataGeneratorInterface
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
            if (is_array($handler) && isset($handler['name'])) {
                $data[$handler['name']] = $path;
            }
        }

        foreach ($routes[1] as $method) {
            foreach ($method as $group) {
                $regex = $group['regex'];
                $parts = explode('|', $regex);
                foreach ($group['routeMap'] as $matchIndex => $routeData) {
                    if (!isset($routeData[0]['name']) || !isset($parts[$matchIndex - 1])) {
                        continue;
                    }

                    $parameters = $routeData[1];
                    $path = $parts[$matchIndex - 1];

                    foreach ($parameters as $parameter) {
                        $path = $this->replaceOnce('([^/]+)', '{' . $parameter . '}', $path);
                    }

                    $path = rtrim($path, '()$~');

                    $data[$routeData[0]['name']] = array(
                        'path' => $path,
                        'params' => $parameters
                    );
                }
            }
        }


        return $data;
    }

    private function replaceOnce($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
