<?php

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

            $cache->write('<?php return ' . var_export($routes, true) . ';');
        }

        return require $cache;
    }
}