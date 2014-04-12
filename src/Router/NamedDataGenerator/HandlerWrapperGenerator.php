<?php

namespace Nice\Router\NamedDataGenerator;

use FastRoute\DataGenerator;
use Nice\Router\NamedDataGeneratorInterface;

class HandlerWrapperGenerator implements NamedDataGeneratorInterface
{
    /**
     * @var \FastRoute\DataGenerator
     */
    private $wrappedGenerator;

    public function __construct(DataGenerator $wrappedGenerator)
    {
        $this->wrappedGenerator = $wrappedGenerator;
    }
    
    /**
     * Adds a route to the data generator. The route data uses the
     * same format that is returned by RouterParser::parser().
     *
     * The handler doesn't necessarily need to be a callable, it
     * can be arbitrary data that will be returned when the route
     * matches.
     *
     * @param string $httpMethod
     * @param array  $routeData
     * @param mixed  $handler
     */
    public function addRoute($httpMethod, $routeData, $handler)
    {
        $this->wrappedGenerator->addRoute($httpMethod, $routeData, $handler);
    }

    /**
     * Returns dispatcher data in some unspecified format, which
     * depends on the used method of dispatch.
     */
    public function getData()
    {
        return $this->wrappedGenerator->getData();
    }

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
    public function addNamedRoute($name, $httpMethod, $routeData, $handler)
    {
        $handler = array(
            'name' => $name,
            'handler' => $handler
        );

        $this->addRoute($httpMethod, $routeData, $handler);
    }
}