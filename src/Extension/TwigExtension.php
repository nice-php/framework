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
 * Sets up Twig services
 */
class TwigExtension extends Extension
{
    /**
     * @var string
     */
    protected $templateDir;

    /**
     * Constructor
     *
     * @param string $templateDir
     */
    public function __construct($templateDir)
    {
        $this->templateDir = $templateDir;
    }

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container->register('twig.asset_extension', 'Nice\Twig\AssetExtension')
            ->setPublic(false)
            ->addArgument(new Reference('service_container'));

        $container->register('twig.app_extension', 'Nice\Twig\ApplicationExtension')
            ->setPublic(false)
            ->addArgument(new Reference('service_container'));

        $container->setParameter('twig.template_dir', $this->templateDir);
        $container->register('twig.loader', 'Twig_Loader_Filesystem')
            ->addArgument(array('%twig.template_dir%'));

        $container->register('twig', 'Twig_Environment')
            ->addArgument(new Reference('twig.loader'))
            ->addMethodCall('addExtension', array(new Reference('twig.asset_extension')))
            ->addMethodCall('addExtension', array(new Reference('twig.app_extension')));
    }
}
