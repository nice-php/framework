<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\DoctrineDbalConfiguration;
use Symfony\Component\Config\Definition\Processor;

class DoctrineDbalConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiresOneElement()
    {
        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'The child node "database" at path "doctrine" must be configured.'
        );

        $processor = new Processor();
        $config = $processor->processConfiguration(new DoctrineDbalConfiguration(), array(array()));
    }
}
