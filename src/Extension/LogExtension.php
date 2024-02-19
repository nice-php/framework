<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Sets up Logging related services
 */
class LogExtension extends Extension
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
     * @return LogConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new LogConfiguration();
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

        foreach ($config['channels'] as $name => $channelConfig) {
            $channelConfig['name'] = $name;
            switch ($channelConfig['handler']) {
                case 'stream':
                    $this->configureStreamChannel($channelConfig, $container);

                    break;

                case 'error_log':
                    $this->configureErrorLogChannel($channelConfig, $container);

                    break;
            }
        }
    }

    private function configureStreamChannel(array $channelConfig, ContainerBuilder $container)
    {
        $options = $channelConfig['options'];

        if (!isset($options['file'])) {
            throw new \RuntimeException('The option "file" must be specified for the stream handler.');
        }

        $name = $channelConfig['name'];
        $level = (int) $channelConfig['level'];
        $file = $options['file'];

        $loggerService = 'logger.'.$name;
        $handlerService = 'logger.'.$name.'.handler';

        $container->register($handlerService)
            ->setClass('Monolog\Handler\StreamHandler')
            ->addArgument($file)
            ->addArgument($level);

        $container->register($loggerService)
            ->setClass('Monolog\Logger')
            ->addArgument($name)
            ->addMethodCall('pushHandler', array(new Reference($handlerService)))
            ->setPublic(true);
    }

    private function configureErrorLogChannel(array $channelConfig, ContainerBuilder $container)
    {
        $name = $channelConfig['name'];
        $level = (int) $channelConfig['level'];

        $loggerService = 'logger.'.$name;
        $handlerService = 'logger.'.$name.'.handler';

        $container->register($handlerService)
            ->setClass('Monolog\Handler\ErrorLogHandler')
            ->addArgument(0)
            ->addArgument($level);

        $container->register($loggerService)
            ->setClass('Monolog\Logger')
            ->addArgument($name)
            ->addMethodCall('pushHandler', array(new Reference($handlerService)));
    }
}
