<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests\Extension;

use Nice\Extension\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the RouterExtension
     */
    public function testConfigure()
    {
        $extension = new SecurityExtension(array(
            'firewall' => '.*',
            'username' => 'user',
            'password' => 'pass',
            'login_path' => '/login',
            'success_path' => '/',
            'logout_path' => '/logout',
            'token_session_key' => '__authed'
        ));

        $container = new ContainerBuilder();
        $extension->load(array(), $container);

        $this->assertTrue($container->hasDefinition('security.firewall_matcher'));
        $this->assertEquals('.*', $container->getDefinition('security.firewall_matcher')->getArgument(0));
        
        $this->assertTrue($container->hasDefinition('security.auth_matcher'));
        $this->assertEquals('/login', $container->getDefinition('security.auth_matcher')->getArgument(0));
        $this->assertEquals('POST', $container->getDefinition('security.auth_matcher')->getArgument(2));
        
        $this->assertTrue($container->hasDefinition('security.logout_matcher'));
        $this->assertEquals('/logout', $container->getDefinition('security.logout_matcher')->getArgument(0));
        
        $this->assertTrue($container->hasDefinition('security.authenticator'));
        $this->assertEquals('user', $container->getDefinition('security.authenticator')->getArgument(0));
        $this->assertEquals('pass', $container->getDefinition('security.authenticator')->getArgument(1));

        $this->assertTrue($container->hasDefinition('security.security_subscriber'));
        $subscriberDefinition = $container->getDefinition('security.security_subscriber');
        $this->assertEquals('security.firewall_matcher', $subscriberDefinition->getArgument(0));
        $this->assertEquals('security.auth_matcher', $subscriberDefinition->getArgument(1));
        $this->assertEquals('security.logout_matcher', $subscriberDefinition->getArgument(2));
        $this->assertEquals('security.authenticator', $subscriberDefinition->getArgument(3));
        $this->assertEquals('/login', $subscriberDefinition->getArgument(4));
        $this->assertEquals('/', $subscriberDefinition->getArgument(5));
        $this->assertEquals('__authed', $subscriberDefinition->getArgument(6));
        $this->assertTrue($container->getDefinition('security.security_subscriber')->hasTag('kernel.event_subscriber'));
    }
    
    public function testGetConfiguration()
    {
        $extension = new SecurityExtension(array(
            'firewall' => '.*',
            'username' => 'user',
            'password' => 'pass'
        ));

        $container = new ContainerBuilder();
        $this->assertInstanceOf('Nice\Extension\SecurityConfiguration', $extension->getConfiguration(array(), $container));
    }
}
