<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\DoctrineKeyValueExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineKeyValueExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the DoctrineOrmExtension
     */
    public function testConfigure()
    {
        $extension = new DoctrineKeyValueExtension(array(
            'key_value' => array(
                'cache_driver' => 'default',
                'mapping' => array(
                    'paths' => array(
                        __DIR__
                    )
                )
            )
        ));

        $container = new ContainerBuilder();
        $extension->load(array(), $container);

        $this->assertTrue($container->has('doctrine.key_value.entity_manager'));
        $this->assertTrue($container->has('doctrine.key_value.configuration'));
    }

    /**
     * Test the getConfiguration method
     */
    public function testGetConfiguration()
    {
        $extension = new DoctrineKeyValueExtension();

        $container = new ContainerBuilder();
        $this->assertInstanceOf('Nice\Extension\DoctrineKeyValueConfiguration', $extension->getConfiguration(array(), $container));
    }
}
