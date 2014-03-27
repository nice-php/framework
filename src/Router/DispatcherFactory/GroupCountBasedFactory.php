<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\DispatcherFactory;

use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\Dispatcher;
use Nice\Router\DispatcherFactoryInterface;
use Nice\Router\RouteCollectorInterface;

class GroupCountBasedFactory implements DispatcherFactoryInterface
{
    /**
     * @var \FastRoute\RouteCollector
     */
    private $collector;

    /**
     * Constructor
     *
     * @param RouteCollectorInterface $collector
     */
    public function __construct(RouteCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * Create a dispatcher
     *
     * @return Dispatcher
     */
    public function create()
    {
        return new GroupCountBased($this->collector->getData());
    }
}
