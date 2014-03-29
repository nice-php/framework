<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Security\Authenticator;

use Nice\Security\Authenticator\SimpleAuthenticator;
use Symfony\Component\HttpFoundation\Request;

class SimpleAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test a successful authentication
     */
    public function testAuthenticationSuccess()
    {
        $request = new Request();
        $request->attributes->set('username', 'user');
        $request->attributes->set('password', 'pass');
        
        $authenticator = new SimpleAuthenticator('user', 'pass');
        
        $this->assertTrue($authenticator->authenticate($request));
    }

    /**
     * Test a failed authentication
     */
    public function testAuthenticationFailure()
    {
        $request = new Request();
        $request->attributes->set('username', 'user');
        $request->attributes->set('password', 'wrong');

        $authenticator = new SimpleAuthenticator('user', 'pass');

        $this->assertFalse($authenticator->authenticate($request));
    }
}
