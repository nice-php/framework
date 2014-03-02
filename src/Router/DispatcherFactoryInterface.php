<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router;

use FastRoute\Dispatcher;

/**
 * Defines the contract any DispatcherFactory must follow
 */
interface DispatcherFactoryInterface
{
    /**
     * Create a dispatcher
     *
     * @return Dispatcher
     */
    public function create();
}
