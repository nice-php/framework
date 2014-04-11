<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\CacheConfiguration;
use Symfony\Component\Config\Definition\Processor;

class CacheConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new CacheConfiguration(), array(array()));

        $this->assertEquals(
            self::getDefaultConfig(),
            $config
        );
    }

    public function testDefaultConnectionConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new CacheConfiguration(), array(array('connections' => array('default' => array('driver' => 'redis')))));

        $this->assertEquals(
            array('connections' => array('default' => array_merge(self::getDefaulConnectionConfig(), array('driver' => 'redis')))),
            $config
        );
    }

    public function testInvalidDriver()
    {
        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Invalid cache driver "fake"'
        );
        
        $processor = new Processor();
        $processor->processConfiguration(new CacheConfiguration(), array(array('connections' => array('default' => array('driver' => 'fake')))));       
    }

    protected static function getDefaultConfig()
    {
        return array(
            'connections' => array(
                
            )
        );
    }

    protected static function getDefaulConnectionConfig()
    {
        return array(
            'namespace' => 'nice:',
            'options' => array(),
        );
    }
}
