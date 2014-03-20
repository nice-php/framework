<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Twig;

use Nice\Twig\AssetExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class AssetExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test a simple example
     */
    public function testPrependsBasePath()
    {
        $extension = $this->getExtension('/');
        
        $path = $extension->getAssetUrl('some-asset.css');
        
        $this->assertEquals('/some-asset.css', $path);
    }

    /**
     * Test a more complex example
     */
    public function testPrependsBasePathAgain()
    {
        $extension = $this->getExtension('/customer/25/show');

        $path = $extension->getAssetUrl('/assets/css/some-asset.css');

        $this->assertEquals('/assets/css/some-asset.css', $path);
    }

    /**
     * Test that absolute paths are not changed
     */
    public function testDoesNotModifyAbsolutePaths()
    {
        $extension = $this->getExtension('/test/somewhere');

        $path = $extension->getAssetUrl('http://www.example.com/some-asset.css');

        $this->assertEquals('http://www.example.com/some-asset.css', $path);
    }

    /**
     * Test that schemaless paths are not changed
     */
    public function testDoesNotModifyAbsoluteSchemalessPaths()
    {
        $extension = $this->getExtension('/test/somewhere');

        $path = $extension->getAssetUrl('//www.example.com/some-asset.css');

        $this->assertEquals('//www.example.com/some-asset.css', $path);
    }

    /**
     * Test that the function handles a non-existent request
     */
    public function testHandlesAbsentRequest()
    {
        $extension = new AssetExtension(new Container());

        $path = $extension->getAssetUrl('assets/css/some-asset.css');

        $this->assertEquals('assets/css/some-asset.css', $path);
    }

    /**
     * Miscellaneous tests
     */
    public function testBasicMethods()
    {
        $extension = $this->getExtension('/');
        $functions = $extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertEquals('asset', $functions[0]->getName());

        $this->assertEquals('asset', $extension->getName());
    }

    /**
     * @param string $uri The URI to give to the request
     *
     * @return AssetExtension
     */
    public function getExtension($uri)
    {
        $request = Request::create($uri);
        $container = new Container();
        $container->set('request', $request);
        
        return new AssetExtension($container);
    }
}
