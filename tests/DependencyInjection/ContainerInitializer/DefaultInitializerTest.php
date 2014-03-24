<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\DependencyInjection\ContainerInitializer;

use Nice\Application;
use Nice\DependencyInjection\ContainerInitializer\DefaultInitializer;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class DefaultInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test container initialization
     */
    public function testInitializeContainer()
    {
        $initializer = new DefaultInitializer();

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('registerDefaultExtensions'))
            ->setConstructorArgs(array('default', true))
            ->getMock();

        $container = $initializer->initializeContainer($app);
        $this->assertNotNull($container);
    }
}