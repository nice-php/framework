<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\RouteCollector;

use PHPUnit\Framework\TestCase;
use Nice\Router\RouteCollector\SimpleCollector;

class SimpleCollectorTest extends TestCase
{
    /**
     * Test basic functionality
     */
    public function testFunctionality()
    {
        $parser = $this->getMockForAbstractClass('FastRoute\RouteParser');
        $generator = $this->getMockForAbstractClass('FastRoute\DataGenerator');

        $called = false;

        $collector = new SimpleCollector($parser, $generator, function (SimpleCollector $collector) use (&$called) {
                $called = true;
            });

        $collector->getData();

        $this->assertTrue($called);
    }
}
