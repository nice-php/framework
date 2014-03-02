<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\RouterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RouterExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the RouterExtension
     */
    public function testConfigure()
    {
        $extension = new RouterExtension();

        $container = new ContainerBuilder();
        $extension->load(array(), $container);

        $this->assertTrue($container->hasDefinition('router.controller_resolver'));
        $this->assertTrue($container->hasDefinition('http_kernel'));
    }
}
