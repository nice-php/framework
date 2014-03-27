<?php

namespace Nice\Router\RouteCollector;

use Nice\Router\RouteCollectorInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

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
        if (file_exists($cacheFile) && !is_writable($cacheFile)) {
            throw new \RuntimeException(sprintf("Unable to write cache file (%s)", $cacheFile));
        }
        
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
