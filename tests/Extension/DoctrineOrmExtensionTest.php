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
