<?php

namespace Nice;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ScopeInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Application extends ContainerAwareHttpKernel implements ContainerInterface
{
    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * Constructor
     *
     * @param string $environment
     * @param bool   $debug
     */
    public function __construct($environment = 'dev', $debug = false)
    {
        $this->environment = (string) $environment;
        $this->debug       = (bool) $debug;

        $container  = $this->initializeContainer();
        $dispatcher = new ContainerAwareEventDispatcher($container);
        $dispatcher->addSubscriberService('router.dispatcher_subscriber', 'Nice\Router\RouterSubscriber');
        $resolver   = new ControllerResolver();

        parent::__construct($dispatcher, $container, $resolver);
    }

    /**
     * @return ContainerInterface
     */
    protected function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cache = new ConfigCache($this->getCacheDir() . '/' . $class . '.php', $this->debug);
        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->setParameter('app.env', $this->environment);
            $container->setParameter('app.debug', $this->debug);

            $container->register('router.parser', 'FastRoute\RouteParser\Std');
            $container->register('router.data_generator', 'FastRoute\DataGenerator\GroupCountBased');
            $container->register('router.collector', 'FastRoute\RouteCollector')
                ->addArgument(new Reference('router.parser'))
                ->addArgument(new Reference('router.data_generator'));

            $container->register('routes', 'Closure')
                ->setSynthetic(true);

            $container->register('app', 'Symfony\Component\HttpKernel\HttpKernelInterface')
                ->setSynthetic(true);

            $container->register('router.dispatcher_factory', 'Nice\Router\DispatcherFactory\GroupCountBasedFactory')
                ->addArgument(new Reference('router.collector'))
                ->addArgument(new Reference('routes'));

            $container->register('router.dispatcher', 'FastRoute\Dispatcher')
                ->setFactoryService('router.dispatcher_factory')
                ->setFactoryMethod('create');

            $container->register('router.dispatcher_subscriber', 'Nice\Router\RouterSubscriber')
                ->addArgument(new Reference('router.dispatcher'));

            $container->setParameter('twig.template_dir', $this->getRootDir() . '/views');
            $container->register('twig.loader', 'Twig_Loader_Filesystem')
                ->addArgument('%twig.template_dir%');

            $container->register('twig', 'Twig_Environment')
                ->addArgument(new Reference('twig.loader'));

            $container->compile();
            $this->dumpContainer($cache, $container, $class, 'Container');
        }

        require_once $cache;

        $this->container = new $class();
        $this->container->set('app', $this);

        return $this->container;
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder The compiled service container
     *
     * @throws \RuntimeException
     */
    protected function buildContainer()
    {
        foreach (array('cache' => $this->getCacheDir(), 'logs' => $this->getLogDir()) as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)", $name, $dir));
            }
        }

        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);

        return $container;
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        return new ContainerBuilder();
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache      $cache     The config cache
     * @param ContainerBuilder $container The service container
     * @param string           $class     The name of the class to generate
     * @param string           $baseClass The name of the container's base class
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        $dumper  = new PhpDumper($container);
        $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));

        $cache->write($content, $container->getResources());
    }

    /**
     * Helper method to get things going.
     *
     * Inspired by Silex.
     *
     * @param Request $request
     */
    public function run(Request $request = null)
    {
        $request = $request ?: Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * @param Request $request A Request instance
     * @param int     $type    The type of the request
     *                         (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch   Whether to catch exceptions or not
     *
     * @return Response A Response instance
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $request->attributes->set('app', $this);

        return parent::handle($request, $type, $catch);
    }

    /**
     * Get the root directory
     *
     * @todo The use of superglobal and realpath make this basically untestable
     *
     * @return string
     */
    public function getRootDir()
    {
        if (!$this->rootDir) {
            // Assumes application root is one level above web root
            $this->rootDir = realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..');
        }

        return $this->rootDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getRootDir() . '/cache/' . $this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->getRootDir() . '/logs';
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return 'UTF-8';
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets a service.
     *
     * @param string          $id      The service identifier
     * @param object|callable $service The service instance
     * @param string          $scope   The scope of the service
     */
    public function set($id, $service, $scope = self::SCOPE_CONTAINER)
    {
        $this->container->set($id, $service, $scope);
    }

    /**
     * Gets a service.
     *
     * @param string $id              The service identifier
     * @param int    $invalidBehavior The behavior when the service does not exist
     *
     * @return object The associated service
     *
     * @throws InvalidArgumentException          if the service is not defined
     * @throws ServiceCircularReferenceException When a circular reference is detected
     * @throws ServiceNotFoundException          When the service is not defined
     *
     * @see Reference
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->container->get($id, $invalidBehavior);
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param string $id The service identifier
     *
     * @return bool true if the service is defined, false otherwise
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed The parameter value
     *
     * @throws InvalidArgumentException if the parameter is not defined
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Checks if a parameter exists.
     *
     * @param string $name The parameter name
     *
     * @return bool The presence of parameter in container
     */
    public function hasParameter($name)
    {
        return $this->container->hasParameter($name);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     */
    public function setParameter($name, $value)
    {
        $this->container->setParameter($name, $value);
    }

    /**
     * Enters the given scope
     *
     * @param string $name
     */
    public function enterScope($name)
    {
        $this->container->enterScope($name);
    }

    /**
     * Leaves the current scope, and re-enters the parent scope
     *
     * @param string $name
     */
    public function leaveScope($name)
    {
        $this->container->leaveScope($name);
    }

    /**
     * Adds a scope to the container
     *
     * @param ScopeInterface $scope
     */
    public function addScope(ScopeInterface $scope)
    {
        $this->container->addScope($scope);
    }

    /**
     * Whether this container has the given scope
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasScope($name)
    {
        return $this->container->hasScope($name);
    }

    /**
     * Determines whether the given scope is currently active.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isScopeActive($name)
    {
        return $this->container->isScopeActive($name);
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
