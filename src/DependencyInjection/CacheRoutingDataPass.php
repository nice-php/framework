<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CacheRoutingDataPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $pathPrefix = '%app.cache_dir%/%app.env%';

        $definition = $container->getDefinition('router.collector');
        $container->setDefinition('router.collector.wrapped', $definition);
        $container->register('router.collector', 'Nice\Router\RouteCollector\CachedCollector')
            ->addArgument(new Reference('router.collector.wrapped'))
            ->addArgument($pathPrefix.'RouteData.php')
            ->addArgument('%app.debug%');

        $definition = $container->getDefinition('router.url_generator.data_generator');
        $container->setDefinition('router.url_generator.data_generator.wrapped', $definition);
        $container->register('router.url_generator.data_generator', 'Nice\Router\UrlGenerator\CachedDataGenerator')
            ->addArgument(new Reference('router.url_generator.data_generator.wrapped'))
            ->addArgument($pathPrefix.'UrlGeneratorData.php')
            ->addArgument('%app.debug%');
    }
}
