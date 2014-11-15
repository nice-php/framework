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
    private static $redisConfig = array(
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
    );

    private static $memcacheConfig = array(
        'connections' => array(
            'default' => array(
                'driver' => 'memcache',
                'namespace' => 'test:',
                'options' => array(
                    'host' => '10.0.0.155',
                    'port' => '1211'
                )
            ),
            'secondary' => array(
                'driver' => 'memcached',
                'options' => array(
                    'socket' => '/tmp/memcache.sock'
                )
            )
        )
    );

    /**
     * Test the CacheExtension
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
        $container = $this->loadContainerBuilder(self::$redisConfig);
        
        $driverDefinition = $container->getDefinition('cache.default.driver');
        $this->assertEquals('Redis', $driverDefinition->getClass());
        $methodCalls = $driverDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'connect', array('10.0.0.155', '16379', 30));
        
        $cacheDefinition = $container->getDefinition('cache.default');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'setRedis', array('cache.default.driver'));
        $this->assertMethodWillBeCalled($methodCalls, 'setNamespace', array('test:'));

        $driverDefinition = $container->getDefinition('cache.secondary.driver');
        $this->assertEquals('Redis', $driverDefinition->getClass());
        $methodCalls = $driverDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'pconnect', array('/tmp/redis.sock', null, 0));

        $cacheDefinition = $container->getDefinition('cache.secondary');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'setRedis', array('cache.secondary.driver'));
        $this->assertMethodWillBeCalled($methodCalls, 'setNamespace', array('nice:'));
    }

    /**
     * Tests memcache configurations
     */
    public function testMemcacheConfig()
    {
        $container = $this->loadContainerBuilder(self::$memcacheConfig);

        $driverDefinition = $container->getDefinition('cache.default.driver');
        $this->assertEquals('Memcache', $driverDefinition->getClass());
        $methodCalls = $driverDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'addServer', array('10.0.0.155', '1211'));

        $cacheDefinition = $container->getDefinition('cache.default');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'setNamespace', array('test:'));
        $this->assertMethodWillBeCalled($methodCalls, 'setMemcache', array('cache.default.driver'));

        $driverDefinition = $container->getDefinition('cache.secondary.driver');
        $this->assertEquals('Memcached', $driverDefinition->getClass());
        $methodCalls = $driverDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'addServer', array('/tmp/memcache.sock', null));

        $cacheDefinition = $container->getDefinition('cache.secondary');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'setNamespace', array('nice:'));
        $this->assertMethodWillBeCalled($methodCalls, 'setMemcached', array('cache.secondary.driver'));
    }

    protected function loadContainerBuilder($config)
    {
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $extension->load(array($config), $container);

        $this->assertTrue($container->hasDefinition('cache.default'));
        $this->assertTrue($container->hasDefinition('cache.default.driver'));
        $this->assertTrue($container->hasDefinition('cache.secondary'));
        $this->assertTrue($container->hasDefinition('cache.secondary.driver'));

        return $container;
    }

    /**
     * Test array cache configurations
     */
    public function testArrayConfig()
    {
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $extension->load(array(array(
                'connections' => array(
                    'default' => array(
                        'driver' => 'array',
                        'namespace' => 'test:'
                    ),
                    'secondary' => array(
                        'driver' => 'array'
                    )
                )
            )), $container);

        $this->assertTrue($container->hasDefinition('cache.default'));
        $this->assertTrue($container->hasDefinition('cache.secondary'));

        $cacheDefinition = $container->getDefinition('cache.default');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'setNamespace', array('test:'));
        
        $cacheDefinition = $container->getDefinition('cache.secondary');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'setNamespace', array('nice:'));
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

    protected function assertMethodWillBeCalled($methodCalls, $method, $arguments = array())
    {
        $called = false;
        foreach ($methodCalls as $call) {
            if ($call[0] == $method) {
                $this->assertEquals($method, $call[0]);
                $this->assertEquals($arguments, $call[1]);
                $called = true;
                break;
            }

        }

        $this->assertTrue($called);
    }
}
