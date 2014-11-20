<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\SecurityConfiguration;
use Symfony\Component\Config\Definition\Processor;

class SecurityConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new SecurityConfiguration(), array(array('firewall' => '.*', 'authenticator' => array('type' => 'username', 'username' => 'user', 'password' => 'pass'))));

        $this->assertEquals(
            array_merge(array('firewall' => '.*', 'authenticator' => array('type' => 'username', 'username' => 'user', 'password' => 'pass')), self::getDefaultConfig()),
            $config
        );
    }

    protected static function getDefaultConfig()
    {
        return array(
            'login_path' => '/login',
            'success_path' => '/',
            'logout_path' => '/logout',
            'token_session_key' => '__nice.is_authenticated',
        );
    }
}
