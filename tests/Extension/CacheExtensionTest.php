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

        $this->assertConfigCorrect($container, 'Redis', 'default', array('10.0.0.155', '16379', 30), 'test:', 'connect');
        $this->assertConfigCorrect($container, 'Redis', 'secondary', array('/tmp/redis.sock', null, 0), 'nice:', 'pconnect');
    }

    /**
     * Tests memcache configurations
     */
    public function testMemcacheConfig()
    {
        $container = $this->loadContainerBuilder(self::$memcacheConfig);

        $this->assertConfigCorrect($container, 'Memcache', 'default', array('10.0.0.155', '1211'), 'test:', 'addServer');
        $this->assertConfigCorrect($container, 'Memcached', 'secondary', array('/tmp/memcache.sock', null), 'nice:', 'addServer');
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

    protected function assertConfigCorrect(ContainerBuilder $container, $className, $name, $server, $namespace, $connectMethod)
    {
        $driverDefinition = $container->getDefinition('cache.' . $name . '.driver');
        $this->assertEquals($className, $driverDefinition->getClass());
        $methodCalls = $driverDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, $connectMethod, $server);

        $cacheDefinition = $container->getDefinition('cache.' . $name);
        $methodCalls = $cacheDefinition->getMethodCalls();
        $this->assertMethodWillBeCalled($methodCalls, 'setNamespace', array($namespace));
        $this->assertMethodWillBeCalled($methodCalls, 'set' . $className, array('cache.' . $name . '.driver'));
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
