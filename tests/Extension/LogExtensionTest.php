<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\LogExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LogExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the LogExtension
     */
    public function testConfigure()
    {
        $extension = new LogExtension();

        $container = new ContainerBuilder();
        $extension->load(array(), $container);
    }

    /**
     * Test configuration merging functionality
     */
    public function testLoadMergesConfigs()
    {
        $extension = new LogExtension(array(
            'channels' => array(
                'secondary' => array(
                    'handler' => 'error_log'
                )
            )));

        $container = new ContainerBuilder();
        $extension->load(array(array(
                'channels' => array(
                    'default' => array(
                        'handler' => 'error_log'
                    )
                )
            )), $container);

        $this->assertTrue($container->hasDefinition('logger.default'));
        $this->assertTrue($container->hasDefinition('logger.secondary'));
    }

    /**
     * Tests stream and error_log handlers
     */
    public function testStreamAndErrorLogConfig()
    {
        $container = $this->loadContainerBuilder(array(
            'channels' => array(
                'default' => array(
                    'handler' => 'stream',
                    'level' => 500,
                    'options' => array(
                        'file' => '/var/log/file'
                    )
                ),
                'secondary' => array(
                    'handler' => 'error_log',
                    'level' => 300
                )
            )
        ));

        $this->assertConfigCorrect($container, 'Monolog\Handler\StreamHandler', 'default', array('/var/log/file', 500));
        $this->assertConfigCorrect($container, 'Monolog\Handler\ErrorLogHandler', 'secondary', array(0, 300));
    }

    private function assertConfigCorrect(ContainerBuilder $container, $handlerClass, $name, $ctorArgs)
    {
        $handlerDefinition = $container->getDefinition('logger.' . $name . '.handler');
        $this->assertEquals($handlerClass, $handlerDefinition->getClass());
        $this->assertEquals($ctorArgs, $handlerDefinition->getArguments());
    }

    protected function loadContainerBuilder($config)
    {
        $extension = new LogExtension();

        $container = new ContainerBuilder();
        $extension->load(array($config), $container);

        $this->assertTrue($container->hasDefinition('logger.default'));
        $this->assertTrue($container->hasDefinition('logger.default.handler'));
        $this->assertTrue($container->hasDefinition('logger.secondary'));
        $this->assertTrue($container->hasDefinition('logger.secondary.handler'));

        return $container;
    }

    /**
     * Test the LogExtension configuring a stream channel with a missing file
     */
    public function testConfigureStreamWithoutFileFails()
    {
        $extension = new LogExtension(array(
            'channels' => array(
                'default' => array(
                    'handler' => 'stream',
                )
            )
        ));

        $this->setExpectedException('RuntimeException', 'The option "file" must be specified for the stream handler.');

        $container = new ContainerBuilder();
        $extension->load(array(), $container);
    }


    /**
     * Test the getConfiguration method
     */
    public function testGetConfiguration()
    {
        $extension = new LogExtension();

        $container = new ContainerBuilder();
        $this->assertInstanceOf('Nice\Extension\LogConfiguration', $extension->getConfiguration(array(), $container));
    }
}
