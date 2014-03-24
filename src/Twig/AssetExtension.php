<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AssetExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset', array($this, 'getAssetUrl'))
        );
    }

    /**
     * Returns the public path of an asset
     *
     * @param string $path
     *
     * @return string
     */
    public function getAssetUrl($path)
    {
        if (false !== strpos($path, '://') || 0 === strpos($path, '//')) {
            return $path;
        }

        if (!$this->container->has('request')) {
            return $path;
        }

        if ('/' !== substr($path, 0, 1)) {
            $path = '/' . $path;
        }

        $request = $this->container->get('request');
        $path = $request->getBasePath() . $path;

        return $path;
    }

    /**
     * Returns the name of the extension
     *
     * @return string
     */
    public function getName()
    {
        return 'asset';
    }
}
