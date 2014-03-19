<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\SessionExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SessionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the RouterExtension
     */
    public function testConfigure()
    {
        $extension = new SessionExtension();

        $container = new ContainerBuilder();
        $extension->load(array(), $container);

        $this->assertTrue($container->hasDefinition('session'));
        $this->assertTrue($container->hasDefinition('session.session_subscriber'));
        $this->assertTrue($container->getDefinition('session.session_subscriber')->hasTag('kernel.event_subscriber'));
    }
}
