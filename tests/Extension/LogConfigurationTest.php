<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\LogConfiguration;
use Symfony\Component\Config\Definition\Processor;

class LogConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new LogConfiguration(), array(array()));

        $this->assertEquals(
            self::getDefaultConfig(),
            $config
        );
    }

    public function testDefaultChannelConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new LogConfiguration(), array(array('channels' => array('default' => array('handler' => 'stream')))));

        $this->assertEquals(
            array('channels' => array('default' => array_merge(self::getDefaultChannelConfig(), array('handler' => 'stream')))),
            $config
        );
    }

    public function testInvalidHandler()
    {
        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid logging handler "fake"'
        );

        $processor = new Processor();
        $processor->processConfiguration(new LogConfiguration(), array(array('channels' => array('default' => array('handler' => 'fake')))));
    }

    protected static function getDefaultConfig()
    {
        return array(
            'channels' => array(

            ),
        );
    }

    protected static function getDefaultChannelConfig()
    {
        return array(
            'handler'  => 'error_log',
            'level'   => 200,
            'options' => array(),
        );
    }
}
