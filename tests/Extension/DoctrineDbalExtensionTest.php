<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\DoctrineDbalExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineDbalExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the DoctrineDbalExtension
     */
    public function testConfigure()
    {
        $extension = new DoctrineDbalExtension();

        $container = new ContainerBuilder();
        $extension->load(array(
            'doctrine' => array(
                'database' => array(
                    'driver' => 'pdo_mysql'
                )
            )
        ), $container);

        $this->assertTrue($container->has('doctrine.dbal.database_connection'));
        $this->assertTrue($container->has('doctrine.dbal.configuration'));
    }

    /**
     * Test the getConfiguration method
     */
    public function testGetConfiguration()
    {
        $extension = new DoctrineDbalExtension();

        $container = new ContainerBuilder();
        $this->assertInstanceOf('Nice\Extension\DoctrineDbalConfiguration', $extension->getConfiguration(array(), $container));
    }
}
