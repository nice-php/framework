<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\UrlGenerator;

use Symfony\Component\Config\ConfigCache;

class CachedDataGenerator implements DataGeneratorInterface
{
    /**
     * @var DataGeneratorInterface
     */
    private $wrappedGenerator;

    /**
     * @var
     */
    private $cacheFile;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     *
     * @param DataGeneratorInterface $wrappedGenerator
     * @param string                 $cacheFile
     * @param bool                   $debug
     */
    public function __construct(DataGeneratorInterface $wrappedGenerator, $cacheFile, $debug = false)
    {
        $this->wrappedGenerator = $wrappedGenerator;
        $this->cacheFile = $cacheFile;
        $this->debug = $debug;
    }

    /**
     * Get formatted route data for use by a URL generator
     *
     * @return array
     */
    public function getData()
    {
        $cache = new ConfigCache($this->cacheFile, $this->debug);
        if (!$cache->isFresh()) {
            $routes = $this->wrappedGenerator->getData();

            $cache->write('<?php return '.var_export($routes, true).';');
        }

        return require $cache;
    }
}
