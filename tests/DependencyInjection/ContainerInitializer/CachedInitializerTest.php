<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\DependencyInjection\ContainerInitializer;

use PHPUnit\Framework\TestCase;
use Nice\Application;
use Nice\DependencyInjection\ContainerInitializer\CachedInitializer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CachedInitializerTest extends TestCase
{
    /**
     * Test container initialization
     */
    public function testInitializeContainer()
    {
        $initializer = $this->getInitializer(sys_get_temp_dir(), $this->once());

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('registerDefaultExtensions'))
            ->setConstructorArgs(array('cache_init'.sha1(uniqid('cache', true)), false))
            ->getMock();

        $container = $initializer->initializeContainer($app);
        $this->assertNotNull($container);

        return $app;
    }

    /**
     * Test a fresh cache
     *
     * @depends testInitializeContainer
     */
    public function testCacheIsFresh(Application $app)
    {
        $initializer = $this->getInitializer(sys_get_temp_dir(), $this->never());

        $container = $initializer->initializeContainer($app);
        $this->assertNotNull($container);
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
        $tmpdir = sys_get_temp_dir().'/'.md5(uniqid());
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
            ->will($this->returnCallback(function () {
                        return new ContainerBuilder();
                    }));

        return new CachedInitializer($default, $cacheDir);
    }
}
