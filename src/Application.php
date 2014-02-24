<?php

namespace TylerSommer\Nice;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use TylerSommer\Nice\Router\RouterSubscriber;

class Application extends HttpKernel
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface          $container
     * @param EventDispatcherInterface    $dispatcher
     * @param ControllerResolverInterface $resolver
     * @param RequestStack                $requestStack
     *
     * @internal param callable $routeFactory
     */
    public function __construct(
        ContainerInterface $container = null,
        EventDispatcherInterface $dispatcher = null,
        ControllerResolverInterface $resolver = null,
        RequestStack $requestStack = null
    ) {
        $this->container = $container = $container ?: new ContainerBuilder();

        $dispatcher = $dispatcher ?: new ContainerAwareEventDispatcher($container);
        $resolver   = $resolver ?: new ControllerResolver();

        parent::__construct($dispatcher, $resolver, $requestStack);

        $container->register('router.parser', 'FastRoute\RouteParser\Std');
        $container->register('router.data_generator', 'FastRoute\DataGenerator\GroupCountBased');
        $container->register('router.collector', 'FastRoute\RouteCollector')
            ->addArgument(new Reference('router.parser'))
            ->addArgument(new Reference('router.data_generator'));

        $container->register('routes', 'Closure')
            ->setSynthetic(true);

        $container->register('router.dispatcher_factory', 'TylerSommer\Nice\Router\DispatcherFactory\GroupCountBasedFactory')
            ->addArgument(new Reference('router.collector'))
            ->addArgument(new Reference('routes'));

        $container->register('router.dispatcher', 'FastRoute\Dispatcher')
            ->setFactoryService('router.dispatcher_factory')
            ->setFactoryMethod('create');

        $container->register('router.dispatcher_subscriber', 'TylerSommer\Nice\Router\RouterSubscriber')
            ->addArgument(new Reference('router.dispatcher'));

        $dispatcher->addSubscriberService('router.dispatcher_subscriber', 'TylerSommer\Nice\Router\RouterSubscriber');
    }

    /**
     * Helper method to get things going.
     *
     * Inspired by Silex
     */
    public function run()
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
