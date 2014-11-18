<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\RouteCollector;

use Nice\Router\RouteCollectorInterface;
use Symfony\Component\Config\ConfigCache;

/**
 * A cached RouteCollector that wraps another RouteCollector
 */
class CachedCollector implements RouteCollectorInterface
{
    /**
     * @var RouteCollectorInterface
     */
    private $wrappedCollector;

    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     *
     * @param RouteCollectorInterface $wrappedCollector
     * @param string                  $cacheDir
     * @param bool                    $debug
     */
    public function __construct(RouteCollectorInterface $wrappedCollector, $cacheFile, $debug = false)
    {
        $this->wrappedCollector = $wrappedCollector;
        $this->cacheFile = $cacheFile;
        $this->debug = $debug;
    }

    /**
     * Returns the collected route data
     *
     * @return array
     */
    public function getData()
    {
        $cache = new ConfigCache($this->cacheFile, $this->debug);
        if (!$cache->isFresh()) {
            $routes = $this->wrappedCollector->getData();

            // TODO: This seems a fragile way to handle this
            if (!$this->isCacheable($routes)) {
                return $routes;
            }

            $cache->write('<?php return '.var_export($routes, true).';');
        }

        return require $cache;
    }

    /**
     * Ensures route data does not contain any Closures
     *
     * @return bool
     */
    private function isCacheable($data)
    {
        $cacheable = true;
        array_walk_recursive($data, function ($value) use (&$cacheable) {
                if ($value instanceof \Closure) {
                    $cacheable = false;
                }
            });

        return $cacheable;
    }
}
