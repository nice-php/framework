<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice;

use Nice\DependencyInjection\CacheRoutingDataPass;
use Nice\DependencyInjection\ContainerInitializer\CachedInitializer;
use Nice\DependencyInjection\ContainerInitializer\DefaultInitializer;
use Nice\DependencyInjection\ContainerInitializerInterface;
use Nice\DependencyInjection\ExtendableInterface;
use Nice\Extension\RouterExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\DependencyInjection\ScopeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * A Nice Application
 */
class Application implements HttpKernelInterface, ContainerInterface, ExtendableInterface
{
    /**
     * @var bool
     */
    private $cache;

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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array|Extension[]
     */
    private $extensions = array();

    /**
     * @var array
     */
    private $compilerPasses = array();

    /**
     * @var HttpKernelInterface
     */
    private $kernel;

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * Constructor
     *
     * @param string $environment
     * @param bool   $debug
     * @param bool   $cache
     */
    public function __construct($environment = 'dev', $debug = false, $cache = true)
    {
        $this->environment = (string) $environment;
        $this->debug       = (bool) $debug;
        $this->cache       = (bool) $cache;
    }

    /**
     * Boot the Application
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->container = $this->initializeContainer();
        $this->kernel    = $this->container->get('http_kernel');

        $this->booted = true;
    }

    /**
     * Prepend an extension
     *
     * @param ExtensionInterface $extension
     */
    public function prependExtension(ExtensionInterface $extension)
    {
        array_unshift($this->extensions, $extension);
    }

    /**
     * Append an extension
     *
     * @param ExtensionInterface $extension
     */
    public function appendExtension(ExtensionInterface $extension)
    {
        array_push($this->extensions, $extension);
    }

    /**
     * Get an ordered list of extensions
     *
     * @return array|ExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Register default extensions
     *
     * This method allows a subclass to customize default extensions
     */
    protected function registerDefaultExtensions()
    {
        $this->appendExtension(new RouterExtension());

        if ($this->isCacheEnabled()) {
            $this->addCompilerPass(new CacheRoutingDataPass());
        }
    }

    /**
     * Adds a compiler pass.
     *
     * @param CompilerPassInterface $pass A compiler pass
     * @param string                $type The type of compiler pass
     */
    protected function addCompilerPass(CompilerPassInterface $pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION)
    {
        $this->compilerPasses[] = array($pass, $type);
    }

    /**
     * @return ContainerInterface
     */
    protected function initializeContainer()
    {
        $this->registerDefaultExtensions();
        $initializer = $this->getContainerInitializer();
        $this->container = $initializer->initializeContainer($this, $this->extensions, $this->compilerPasses);
        $this->container->set('app', $this);

        return $this->container;
    }

    /**
     * @return ContainerInitializerInterface
     */
    protected function getContainerInitializer()
    {
        $initializer = new DefaultInitializer();
        if ($this->cache) {
            $initializer = new CachedInitializer($initializer, $this->getCacheDir());
        }

        return $initializer;
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

        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }
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
        if (!$this->booted) {
            $this->boot();
        }

        $request->attributes->set('app', $this);

        return $this->kernel->handle($request, $type, $catch);
    }

    /**
     * Get the root directory
     *
     * @return string
     */
    public function getRootDir()
    {
        if (!$this->rootDir) {
            $refl = new \ReflectionObject($this);
            $filename = $refl->getFileName();
            if (false !== ($pos = strrpos($filename, '/vendor/'))) {
                $filename = substr($filename, 0, $pos);
            } else {
                $filename = dirname($filename).'/..';
            }

            $this->rootDir = str_replace('\\', '/', $filename);
        }

        return $this->rootDir;
    }

    /**
     * @return string|null Null if Caching should be disabled
     */
    public function getCacheDir()
    {
        return $this->cache
            ? $this->getRootDir().'/cache/'.$this->environment
            : null;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->getRootDir().'/logs';
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
        if (!$this->booted) {
            $this->boot();
        }

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
        if (!$this->booted) {
            $this->boot();
        }

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
        if (!$this->booted) {
            $this->boot();
        }

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
        if (!$this->booted) {
            $this->boot();
        }

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
        if (!$this->booted) {
            $this->boot();
        }

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
        if (!$this->booted) {
            $this->boot();
        }

        $this->container->setParameter($name, $value);
    }

    /**
     * Enters the given scope
     *
     * @param string $name
     */
    public function enterScope($name)
    {
        if (!$this->booted) {
            $this->boot();
        }

        $this->container->enterScope($name);
    }

    /**
     * Leaves the current scope, and re-enters the parent scope
     *
     * @param string $name
     */
    public function leaveScope($name)
    {
        if (!$this->booted) {
            $this->boot();
        }

        $this->container->leaveScope($name);
    }

    /**
     * Adds a scope to the container
     *
     * @param ScopeInterface $scope
     */
    public function addScope(ScopeInterface $scope)
    {
        if (!$this->booted) {
            $this->boot();
        }

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
        if (!$this->booted) {
            $this->boot();
        }

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
        if (!$this->booted) {
            $this->boot();
        }

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
     * @return boolean
     */
    public function isCacheEnabled()
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
