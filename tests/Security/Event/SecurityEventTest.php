<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Security\Event;

use Nice\Security\Event\SecurityEvent;
use Symfony\Component\HttpFoundation\Request;

class SecurityEventTest extends \PHPUnit_Framework_TestCase 
{
    public function testGetRequest()
    {
        $request = new Request();
        
        $event = new SecurityEvent($request);
        
        $this->assertSame($request, $event->getRequest());
    }
}
 