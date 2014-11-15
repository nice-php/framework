<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\DoctrineKeyValueConfiguration;
use Symfony\Component\Config\Definition\Processor;

class DoctrineKeyValueConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiresMappingElement()
    {
        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'The child node "key_value" at path "doctrine" must be configured.'
        );

        $processor = new Processor();
        $config = $processor->processConfiguration(new DoctrineKeyValueConfiguration(), array(array()));
    }

    public function testDefaultKeyValueConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new DoctrineKeyValueConfiguration(), array(array('key_value' => array('mapping' => array('paths' => array(__DIR__))))));

        $this->assertEquals(
            array(
                'key_value' => array(
                    'cache_driver' => 'default',
                    'mapping' => array('paths' => array(__DIR__)),
                )
            ),
            $config
        );
    }
}
