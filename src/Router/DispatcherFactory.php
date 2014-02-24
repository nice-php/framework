<?php
namespace TylerSommer\Nice\Router;

use FastRoute\Dispatcher;

/**
 * Defines the contract any DispatcherFactory must follow
 */
interface DispatcherFactory
{
    /**
     * Create a dispatcher
     *
     * @return Dispatcher
     */
    public function create();
}
