<?php

namespace Nice\Router\DispatcherFactory;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Nice\Router\DispatcherFactoryInterface;
use Symfony\Component\Config\ConfigCache;

abstract class CachedDispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * @var \FastRoute\RouteCollector
     */
    private $collector;

    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var bool
     */
    private $collected = false;

    /**
     * Constructor
     *
     * @param RouteCollector $collector
     * @param string         $cacheFile
     * @param bool           $debug
     */
    public function __construct(RouteCollector $collector, $cacheFile, $debug = false)
    {
        $this->collector = $collector;
        $this->cacheFile = $cacheFile;
        $this->debug     = (bool) $debug;
    }

    /**
     * Create a dispatcher
     *
     * @return Dispatcher
     */
    public function create()
    {
        $cache = new ConfigCache($this->cacheFile, $this->debug);
        if (!$cache->isFresh()) {
            if (!$this->collected) {
                $this->collectRoutes($this->collector);
            }

            $cache->write('<?php return ' . var_export($this->collector->getData(), true) . ';');
        }

        $routes = require_once $cache;

        return new Dispatcher\GroupCountBased($routes);
    }

    /**
     * Collect configured routes
     *
     * @param RouteCollector $collector
     */
    protected function collectRoutes(RouteCollector $collector)
    {
        $this->doCollectRoutes($collector);
        $this->collected = true;
    }

    /**
     * Collect configured routes, actually doing the work
     *
     * Implement this method in a subclass
     *
     * @param RouteCollector $collector
     */
    abstract protected function doCollectRoutes(RouteCollector $collector);
}
