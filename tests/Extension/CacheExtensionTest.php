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
     * Test the getConfiguration method
     */
    public function testGetConfiguration()
    {
        $extension = new CacheExtension();

        $container = new ContainerBuilder();
        $this->assertInstanceOf('Nice\Extension\CacheConfiguration', $extension->getConfiguration(array(), $container));
    }
}
