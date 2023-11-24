<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\DependencyInjection\ConfigurationProvider;

use PHPUnit\Framework\TestCase;
use Nice\DependencyInjection\ConfigurationProvider\NullConfigurationProvider;

class NullConfigurationProviderTest extends TestCase
{
    /**
     * Test no-operation functionality
     */
    public function testNoOp()
    {
        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->never())
            ->method($this->anything());

        $provider = new NullConfigurationProvider();
        $provider->load($container);
    }
}
