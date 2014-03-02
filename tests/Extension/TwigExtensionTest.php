<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the TwigExtension
     */
    public function testConfigure()
    {
        $extension = new TwigExtension('/path');

        $container = new ContainerBuilder();
        $extension->load(array(), $container);

        $this->assertEquals('/path', $container->getParameter('twig.template_dir'));
        $this->assertTrue($container->hasDefinition('twig'));
    }
}
