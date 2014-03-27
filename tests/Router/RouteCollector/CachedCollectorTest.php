<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\RouteCollector;

use Nice\Router\RouteCollector\CachedCollector;

class CachedCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic caching
     */
    public function testCreateWritesCache()
    {
        $filename = sys_get_temp_dir() . '/_collector' . sha1(uniqid('_collector', true));
        $collector = $this->getCollector($filename, $this->once());

        $data = $collector->getData();

        $this->assertNotEmpty(file_get_contents($filename));
        $this->assertEquals(array(array(), array()), $data);
        
        return $filename;
    }

    /**
     * Test create with a fresh cache
     * 
     * @depends testCreateWritesCache
     */
    public function testCreateWithFreshCache($filename)
    {
        $collector = $this->getCollector($filename, $this->never());

        $collector->getData();
    }

    /**
     * Test an unwriteable file
     * 
     * @todo This relies on something outside of Nice throwing the exception
     */
    public function testUnableToWriteCache()
    {
        $collector = $this->getCollector('/some/unwriteable/path');
        
        $this->setExpectedException('RuntimeException', 'Failed to create "/some/unwriteable".');
        
        $collector->getData();
    }

    /**
     * @param string $filename
     * @param null   $expects
     *
     * @return CachedCollector
     */
    protected function getCollector($filename, $expects = null)
    {
        $routeCollector = $this->getMockBuilder('Nice\Router\RouteCollectorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects($expects ?: $this->any())
            ->method('getData')
            ->will($this->returnValue(array(array(), array())));

        $collector = new CachedCollector($routeCollector, $filename, false);

        return $collector;
    }
}