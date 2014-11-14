<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\DoctrineOrmExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineOrmExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the DoctrineOrmExtension
     */
    public function testConfigure()
    {
        $extension = new DoctrineOrmExtension();

        $container = new ContainerBuilder();
        $extension->load(array(
            'doctrine' => array(
                'database' => array(
                    'driver' => 'pdo_mysql'
                ),
                'mapping' => array(
                    'paths' => array(
                        __DIR__
                    )
                )
            )
        ), $container);

        $this->assertTrue($container->has('doctrine.orm.entity_manager'));
        $this->assertTrue($container->has('doctrine.orm.configuration'));
        $this->assertTrue($container->has('doctrine.dbal.database_connection'));
        $this->assertTrue($container->has('doctrine.dbal.configuration'));
        $this->assertCount(2, $container->getDefinition('doctrine.orm.configuration')->getMethodCalls());
    }

    /**
     * Test the DoctrineOrmExtension
     */
    public function testConfigureWithCache()
    {
        $extension = new DoctrineOrmExtension();

        $container = new ContainerBuilder();
        $container->setParameter('app.cache', true);
        $extension->load(array(
            'doctrine' => array(
                'database' => array(
                    'driver' => 'pdo_mysql'
                ),
                'mapping' => array(
                    'paths' => array(
                        __DIR__
                    )
                )
            )
        ), $container);

        $calls = $container->getDefinition('doctrine.orm.configuration')->getMethodCalls();
        $this->assertCount(3, $calls);
        $lastMethodCall = array_pop($calls);
        $this->assertEquals(array('setProxyDir', array('%app.cache_dir%/doctrine')), $lastMethodCall);
    }

    /**
     * Test the getConfiguration method
     */
    public function testGetConfiguration()
    {
        $extension = new DoctrineOrmExtension();

        $container = new ContainerBuilder();
        $this->assertInstanceOf('Nice\Extension\DoctrineOrmConfiguration', $extension->getConfiguration(array(), $container));
    }
}
