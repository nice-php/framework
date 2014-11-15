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
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $extension->load(array(self::$redisConfig), $container);
        
        $this->assertTrue($container->hasDefinition('cache.default'));
        $this->assertTrue($container->hasDefinition('cache.default.driver'));
        $this->assertTrue($container->hasDefinition('cache.secondary'));
        $this->assertTrue($container->hasDefinition('cache.secondary.driver'));
        
        $driverDefinition = $container->getDefinition('cache.default.driver');
        $methodCalls = $driverDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('Redis', $driverDefinition->getClass());
        $this->assertEquals('connect', $methodName);
        $this->assertEquals(array('10.0.0.155', '16379', 30), $methodArgs);
        
        $cacheDefinition = $container->getDefinition('cache.default');
        $methodCalls = $cacheDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('setRedis', $methodName);
        $this->assertEquals('cache.default.driver', $methodArgs[0]);
        list($methodName, $methodArgs) = $methodCalls[1];
        $this->assertEquals('setNamespace', $methodName);
        $this->assertEquals('test:', $methodArgs[0]);

        $driverDefinition = $container->getDefinition('cache.secondary.driver');
        $methodCalls = $driverDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('Redis', $driverDefinition->getClass());
        $this->assertEquals('pconnect', $methodName);
        $this->assertEquals(array('/tmp/redis.sock', null, 0), $methodArgs);

        $cacheDefinition = $container->getDefinition('cache.secondary');
        $methodCalls = $cacheDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('setRedis', $methodName);
        $this->assertEquals('cache.secondary.driver', $methodArgs[0]);
        list($methodName, $methodArgs) = $methodCalls[1];
        $this->assertEquals('setNamespace', $methodName);
        $this->assertEquals('nice:', $methodArgs[0]);
    }

    /**
     * Tests memcache configurations
     */
    public function testMemcacheConfig()
    {
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $extension->load(array(self::$memcacheConfig), $container);

        $this->assertTrue($container->hasDefinition('cache.default'));
        $this->assertTrue($container->hasDefinition('cache.default.driver'));
        $this->assertTrue($container->hasDefinition('cache.secondary'));
        $this->assertTrue($container->hasDefinition('cache.secondary.driver'));

        $driverDefinition = $container->getDefinition('cache.default.driver');
        $methodCalls = $driverDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('Memcache', $driverDefinition->getClass());
        $this->assertEquals('addServer', $methodName);
        $this->assertEquals(array('10.0.0.155', '1211'), $methodArgs);

        $cacheDefinition = $container->getDefinition('cache.default');
        $methodCalls = $cacheDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('setNamespace', $methodName);
        $this->assertEquals('test:', $methodArgs[0]);
        list($methodName, $methodArgs) = $methodCalls[1];
        $this->assertEquals('setMemcache', $methodName);
        $this->assertEquals('cache.default.driver', $methodArgs[0]);

        $driverDefinition = $container->getDefinition('cache.secondary.driver');
        $methodCalls = $driverDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('Memcached', $driverDefinition->getClass());
        $this->assertEquals('addServer', $methodName);
        $this->assertEquals(array('/tmp/memcache.sock', null), $methodArgs);

        $cacheDefinition = $container->getDefinition('cache.secondary');
        $methodCalls = $cacheDefinition->getMethodCalls();
        list($methodName, $methodArgs) = $methodCalls[0];
        $this->assertEquals('setNamespace', $methodName);
        $this->assertEquals('nice:', $methodArgs[0]);
        list($methodName, $methodArgs) = $methodCalls[1];
        $this->assertEquals('setMemcached', $methodName);
        $this->assertEquals('cache.secondary.driver', $methodArgs[0]);
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
        $methodName = $methodCalls[0][0];
        $methodArgs = $methodCalls[0][1];
        $this->assertEquals('setNamespace', $methodName);
        $this->assertEquals('test:', $methodArgs[0]);
        
        $cacheDefinition = $container->getDefinition('cache.secondary');
        $methodCalls = $cacheDefinition->getMethodCalls();
        $methodName = $methodCalls[0][0];
        $methodArgs = $methodCalls[0][1];
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
