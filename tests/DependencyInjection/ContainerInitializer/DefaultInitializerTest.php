<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\DependencyInjection\ContainerInitializer;

use Nice\Application;
use Nice\DependencyInjection\ContainerInitializer\DefaultInitializer;
use Symfony\Component\DependencyInjection\Container;

class DefaultInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test container initialization
     */
    public function testInitializeContainer()
    {
        $initializer = new DefaultInitializer();

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array(
                    'registerDefaultExtensions', 
                    'getRootDir', 
                    'getLogDir',
                    'getCacheDir',
                    'getEnvironment',
                    'isDebug',
                    'isCacheEnabled'
                ))
            ->disableOriginalConstructor()
            ->getMock();
        $app->expects($this->atLeastOnce())->method('getRootDir')
            ->will($this->returnValue('/some/path'));
        $app->expects($this->atLeastOnce())->method('getLogDir')
            ->will($this->returnValue('/some/path/logs'));
        $app->expects($this->atLeastOnce())->method('getCacheDir')
            ->will($this->returnValue('/some/path/cache'));
        $app->expects($this->atLeastOnce())->method('getEnvironment')
            ->will($this->returnValue('env'));
        $app->expects($this->atLeastOnce())->method('isDebug')
            ->will($this->returnValue(true));
        $app->expects($this->atLeastOnce())->method('isCacheEnabled')
            ->will($this->returnValue(true));

        $container = $initializer->initializeContainer($app);
        $this->assertNotNull($container);

        $this->assertEquals('/some/path', $container->getParameter('app.root_dir'));
        $this->assertEquals('/some/path/logs', $container->getParameter('app.log_dir'));
        $this->assertEquals('/some/path/cache', $container->getParameter('app.cache_dir'));
        $this->assertEquals('env', $container->getParameter('app.env'));
        $this->assertTrue($container->getParameter('app.debug'));
        $this->assertTrue($container->getParameter('app.cache'));
        
        $this->assertTrue($container->has('event_dispatcher'));
        $this->assertTrue($container->has('app'));
        $this->assertTrue($container->has('request'));
    }
}
