<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\DependencyInjection\ConfigurationProvider;

use Nice\DependencyInjection\ConfigurationProvider\FileConfigurationProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FileConfigurationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test no-operation functionality
     */
    public function testNoOp()
    {
        $container = new ContainerBuilder();

        $provider = new FileConfigurationProvider(__DIR__.'/../../Mocks/config.yml');
        $provider->load($container);

        $this->assertTrue($container->has('test'));
        $this->assertEquals('stdClass', $container->getDefinition('test')->getClass());
    }
}
