<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests;

use Nice\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the instantiation
     */
    public function testInstantiation()
    {
        $app = new Application('test', true);
        
        $this->assertTrue($app->isDebug());
        $this->assertEquals('test', $app->getEnvironment());
    }
}
