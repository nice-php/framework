<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Router\UrlGenerator;

use Nice\Router\UrlGenerator\CachedDataGenerator;

class CachedDataGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic caching
     */
    public function testCreateWritesCache()
    {
        $filename = sys_get_temp_dir() . '/_collector' . sha1(uniqid('_collector', true));
        $generator = $this->getGenerator($filename, $this->once());

        $data = $generator->getData();

        $this->assertNotEmpty(file_get_contents($filename));
        $this->assertEquals(array(), $data);

        return $filename;
    }

    /**
     * Test create with a fresh cache
     *
     * @depends testCreateWritesCache
     */
    public function testCreateWithFreshCache($filename)
    {
        $generator = $this->getGenerator($filename, $this->never());

        $generator->getData();
    }

    /**
     * Test an unwriteable file
     *
     * @todo This relies on something outside of Nice throwing the exception
     */
    public function testUnableToWriteCache()
    {
        $generator = $this->getGenerator('/some/unwriteable/path');

        $this->setExpectedException('RuntimeException', 'Failed to create');

        $generator->getData();
    }

    /**
     * @param string $filename
     * @param null   $expects
     * @param array  $routes
     *
     * @return CachedDataGenerator
     */
    protected function getGenerator($filename, $expects = null, $routes = array())
    {
        $generator = $this->getMockForAbstractClass('Nice\Router\UrlGenerator\DataGeneratorInterface');
        $generator->expects($expects ?: $this->any())
            ->method('getData')
            ->will($this->returnValue($routes));
        
        return new CachedDataGenerator($generator, $filename, false);
    }
}
