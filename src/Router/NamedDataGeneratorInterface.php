<?php

namespace Nice\Router;

use FastRoute\DataGenerator;

interface NamedDataGeneratorInterface extends DataGenerator
{
    /**
     * Adds a named route to the data generator
     *
     * The handler doesn't necessarily need to be a callable, it
     * can be arbitrary data that will be returned when the route
     * matches.
     *
     * @param string $name
     * @param string $httpMethod
     * @param array  $routeData
     * @param mixed  $handler
     *
     * @return void
     */
    public function addNamedRoute($name, $httpMethod, $routeData, $handler);
}