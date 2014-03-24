<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\DependencyInjection\ContainerInitializer;

use Nice\Application;
use Nice\DependencyInjection\ContainerInitializer\CachedInitializer;
use Nice\DependencyInjection\ContainerInitializer\DefaultInitializer;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class CachedInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test container initialization
     */
    public function testInitializeContainer()
    {
        $initializer = $this->getInitializer(sys_get_temp_dir());
        
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('registerDefaultExtensions'))
            ->setConstructorArgs(array('cache_init', true))
            ->getMock();

        $container = $initializer->initializeContainer($app);
        $this->assertNotNull($container);
        
        return $initializer;
    }

    /**
     * Test a fresh cache
     */
    public function testCacheIsFresh()
    {
        $initializer = $this->getInitializer(sys_get_temp_dir());

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('registerDefaultExtensions'))
            ->setConstructorArgs(array('cache_fresh', true))
            ->getMock();

        $firstContainer = $initializer->initializeContainer($app);
        $this->assertNotNull($firstContainer);

        $initializer = $this->getInitializer(sys_get_temp_dir(), $this->never());

        $secondContainer = $initializer->initializeContainer($app);
        
        $this->assertNotSame($secondContainer, $firstContainer);
    }

    /**
     * Test failure to create cache directory
     */
    public function testFailureToCreateCacheDir()
    {
        $this->setExpectedException('RuntimeException', 'Unable to create the cache directory');
        
        $this->getInitializer('/someunwriteable/path');
    }

    /**
     * Test failure to write to cache directory
     */
    public function testFailureToWriteCacheDir()
    {
        $tmpdir = sys_get_temp_dir() . '/' . md5(uniqid());
        mkdir($tmpdir, 0700, true);
        chmod($tmpdir, 0000);

        $this->setExpectedException('RuntimeException', 'Unable to write in the cache directory');

        $this->getInitializer($tmpdir);
    }

    /**
     * @param string $cacheDir
     *
     * @return CachedInitializer
     */
    private function getInitializer($cacheDir, $expects = null)
    {
        $default = $this->getMockForAbstractClass('Nice\DependencyInjection\ContainerInitializerInterface');
        $default->expects($expects ?: $this->any())->method('initializeContainer')
            ->will($this->returnCallback(function() {
                        return new ContainerBuilder();
                    }));
        
        return new CachedInitializer($default, $cacheDir);
    }
}