<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\CacheExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CacheExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the SecurityExtension
     */
    public function testConfigure()
    {
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $extension->load(array(), $container);
    }

    /**
     * Test configuration merging functionality
     */
    public function testLoadMergesConfigs()
    {
        $extension = new CacheExtension(array(
            'connections' => array(
                'secondary' => array(
                    'driver' => 'array'
                )
            )));

        $container = new ContainerBuilder();
        $extension->load(array(array(
                'connections' => array(
                    'default' => array(
                        'driver' => 'array'
                    )
                )
            )), $container);

        $this->assertTrue($container->hasDefinition('cache.default'));
        $this->assertTrue($container->hasDefinition('cache.secondary'));
    }

    /**
     * Tests redis configurations
     */
    public function testRedisConfig()
    {
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $extension->load(array(array(
                'connections' => array(
                    'default' => array(
                        'driver' => 'redis',
                        'namespace' => 'test:',
                        'options' => array(
                            'host' => '10.0.0.155',
                            'port' => '16379',
                            'timeout' => 30,
                            'persistent' => false
                        )
                    ),
                    'secondary' => array(
                        'driver' => 'redis',
                        'options' => array(
                            'socket' => '/tmp/redis.sock',
                            'persistent' => true
                        )
                    )
                )
            )), $container);
        
        $this->assertTrue($container->hasDefinition('cache.default'));
        $this->assertTrue($container->hasDefinition('cache.default.driver'));
        $this->assertTrue($container->hasDefinition('cache.secondary'));
        $this->assertTrue($container->hasDefinition('cache.secondary.driver'));
        
        $driverDefinition = $container->getDefinition('cache.default.driver');
        $methodCalls = $driverDefinition->getMethodCalls();
        $methodName = $methodCalls[0][0];
        $methodArgs = $methodCalls[0][1];
        $this->assertEquals('Redis', $driverDefinition->getClass());
        $this->assertEquals('connect', $methodName);
        $this->assertEquals('10.0.0.155', $methodArgs[0]);
        $this->assertEquals('16379', $methodArgs[1]);
        $this->assertEquals(30, $methodArgs[2]);
        
        $cacheDefinition = $container->getDefinition('cache.default');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $methodName = $methodCalls[0][0];
        $methodArgs = $methodCalls[0][1];
        $this->assertEquals('setRedis', $methodName);
        $this->assertEquals('cache.default.driver', $methodArgs[0]);
        $methodName = $methodCalls[1][0];
        $methodArgs = $methodCalls[1][1];
        $this->assertEquals('setNamespace', $methodName);
        $this->assertEquals('test:', $methodArgs[0]);

        $driverDefinition = $container->getDefinition('cache.secondary.driver');
        $methodCalls = $driverDefinition->getMethodCalls();
        $methodName = $methodCalls[0][0];
        $methodArgs = $methodCalls[0][1];
        $this->assertEquals('Redis', $driverDefinition->getClass());
        $this->assertEquals('pconnect', $methodName);
        $this->assertEquals('/tmp/redis.sock', $methodArgs[0]);
        $this->assertEquals(null, $methodArgs[1]);
        $this->assertEquals(0, $methodArgs[2]);

        $cacheDefinition = $container->getDefinition('cache.secondary');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $methodName = $methodCalls[0][0];
        $methodArgs = $methodCalls[0][1];
        $this->assertEquals('setRedis', $methodName);
        $this->assertEquals('cache.secondary.driver', $methodArgs[0]);
        $methodName = $methodCalls[1][0];
        $methodArgs = $methodCalls[1][1];
        $this->assertEquals('setNamespace', $methodName);
        $this->assertEquals('nice:', $methodArgs[0]);
    }
    
    /**
     * Test the getConfiguration method
     */
    public function testGetConfiguration()
    {
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $this->assertInstanceOf('Nice\Extension\CacheConfiguration', $extension->getConfiguration(array(), $container));
    }
}
