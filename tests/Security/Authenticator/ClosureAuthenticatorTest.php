<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Security\Authenticator;

use Nice\Security\Authenticator\ClosureAuthenticator;
use Symfony\Component\HttpFoundation\Request;

class ClosureAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test a successful authentication
     */
    public function testAuthenticationSuccess()
    {
        $request = new Request();
        
        $authenticator = new ClosureAuthenticator(function(Request $request) {
            return true;
        });
        
        $this->assertTrue($authenticator->authenticate($request));
    }

    /**
     * Test a failed authentication
     */
    public function testAuthenticationFailure()
    {
        $request = new Request();

        $authenticator = new ClosureAuthenticator(function(Request $request) {
            return false;
        });

        $this->assertFalse($authenticator->authenticate($request));
    }
}
