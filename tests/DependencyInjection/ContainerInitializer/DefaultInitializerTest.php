<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\DependencyInjection\ContainerInitializer;

use Nice\Application;
use Nice\DependencyInjection\CompilerAwareExtensionInterface;
use Nice\DependencyInjection\ConfigurationProvider\NullConfigurationProvider;
use Nice\DependencyInjection\ContainerInitializer\DefaultInitializer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DefaultInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test container initialization
     */
    public function testInitializeContainer()
    {
        $initializer = new DefaultInitializer(new NullConfigurationProvider());

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array(
                    'registerDefaultExtensions',
                    'getRootDir',
                    'getLogDir',
                    'getCacheDir',
                    'getEnvironment',
                    'isDebug',
                    'isCacheEnabled',
                ))
            ->disableOriginalConstructor()
            ->getMock();
        $app->expects($this->atLeastOnce())->method('getRootDir')
            ->will($this->returnValue('/some/path'));
        $app->expects($this->atLeastOnce())->method('getLogDir')
            ->will($this->returnValue('/some/path/logs'));
        $app->expects($this->atLeastOnce())->method('getCacheDir')
            ->will($this->returnValue('/some/path/cache'));
        $app->expects($this->atLeastOnce())->method('getEnvironment')
            ->will($this->returnValue('env'));
        $app->expects($this->atLeastOnce())->method('isDebug')
            ->will($this->returnValue(true));
        $app->expects($this->atLeastOnce())->method('isCacheEnabled')
            ->will($this->returnValue(true));

        $container = $initializer->initializeContainer($app, array(new TestExtension()), array(new TestCompilerPass()));
        $this->assertNotNull($container);

        $this->assertEquals('/some/path', $container->getParameter('app.root_dir'));
        $this->assertEquals('/some/path/logs', $container->getParameter('app.log_dir'));
        $this->assertEquals('/some/path/cache', $container->getParameter('app.cache_dir'));
        $this->assertEquals('env', $container->getParameter('app.env'));
        $this->assertTrue($container->getParameter('app.debug'));
        $this->assertTrue($container->getParameter('app.cache'));

        $this->assertTrue($container->has('event_dispatcher'));
        $this->assertTrue($container->has('app'));
        $this->assertTrue($container->has('request'));

        $this->assertTrue($container->has('test'));
        $this->assertTrue($container->has('aware'));
    }
}

class TestCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $serviceName;

    public function __construct($serviceName = 'test')
    {
        $this->serviceName = $serviceName;
    }

    public function process(ContainerBuilder $container)
    {
        $container->register($this->serviceName, '\stdClass');
    }
}

class TestExtension extends Extension implements CompilerAwareExtensionInterface
{
    /**
     * Gets the CompilerPasses this extension requires.
     *
     * @return array|CompilerPassInterface[]
     */
    public function getCompilerPasses()
    {
        return new TestCompilerPass('aware');
    }

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $config, ContainerBuilder $container)
    {
    }
}
