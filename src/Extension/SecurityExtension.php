<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Sets up a firewall
 */
class SecurityExtension extends Extension
{
    /**
     * @var array
     */
    private $options = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Returns extension configuration
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return SecurityConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new SecurityConfiguration();
    }

    /**
     * Loads a specific configuration.
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs[] = $this->options;
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->register('security.firewall_matcher', 'Symfony\Component\HttpFoundation\RequestMatcher')
            ->setPublic(false)
            ->addArgument($config['firewall']);
        $container->register('security.auth_matcher', 'Symfony\Component\HttpFoundation\RequestMatcher')
            ->setPublic(false)
            ->addArgument($config['login_path'])
            ->addArgument(null)
            ->addArgument('POST');
        $container->register('security.logout_matcher', 'Symfony\Component\HttpFoundation\RequestMatcher')
            ->setPublic(false)
            ->addArgument($config['logout_path']);

        $authenticatorService = $this->configureAuthenticator($config['authenticator'], $container);

        $container->register('security.security_subscriber', 'Nice\Security\FirewallSubscriber')
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument(new Reference('security.firewall_matcher'))
            ->addArgument(new Reference('security.auth_matcher'))
            ->addArgument(new Reference('security.logout_matcher'))
            ->addArgument(new Reference($authenticatorService))
            ->addArgument($config['login_path'])
            ->addArgument($config['success_path'])
            ->addArgument($config['token_session_key'])
            ->addTag('kernel.event_subscriber');

        $container->register('security.auth_failure_subscriber', 'Nice\Security\AuthenticationFailureSubscriber')
            ->addTag('kernel.event_subscriber');
    }

    private function configureAuthenticator(array $config, ContainerBuilder $container)
    {
        $authenticatorService = 'security.authenticator';
        switch ($config['type']) {
            case 'username':
                if (!isset($config['username']) || !isset($config['password'])) {
                    throw new \RuntimeException('Username and password is required for the username authenticator');
                }

                $container->register('security.authenticator', 'Nice\Security\Authenticator\SimpleAuthenticator')
                    ->setPublic(false)
                    ->addArgument($config['username'])
                    ->addArgument($config['password']);

                break;

            case 'closure':
                $authenticatorService = 'security.authenticator.real';
                $container->register('security.authenticator.real', 'Nice\Security\Authenticator\ClosureAuthenticator')
                    ->setPublic(false)
                    ->addArgument(new Reference('security.authenticator'));

                $container->register('security.authenticator', 'Closure')
                    ->setSynthetic(true);
        }

        return $authenticatorService;
    }
}
