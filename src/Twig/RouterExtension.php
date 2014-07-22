<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RouterExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list
     *
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'app' => $this->container->get('app')
        );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'current_controller'    => new \Twig_Function_Method($this, 'getController'),
            'current_action'        => new \Twig_Function_Method($this, 'getAction'),
            'current_route'         => new \Twig_Function_Method($this, 'getRoute'),
            'is_current_controller' => new \Twig_Function_Method($this, 'isCurrentController'),
            'is_current_route'      => new \Twig_Function_Method($this, 'isCurrentRoute'),
            'path'                  => new \Twig_Function_Method($this, 'generateUrl')
        );
    }

    /**
     * Returns true if the user is currently on the given controller(s)
     *
     * @param string|array $controllers
     *
     * @return bool
     */
    public function isCurrentController($controllers)
    {
        $controllers = is_array($controllers) ? $controllers : array($controllers);

        foreach ($controllers as $controller) {
            if ($this->getController() == $controller) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the given route matches the current route
     *
     * @param string $route
     *
     * @return bool
     */
    public function isCurrentRoute($route)
    {
        return $route === $this->getRoute();
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->getCurrentRequest()->get('_route');
    }

    /**
     * @return string
     */
    public function getController()
    {
        if (!$this->controller) {
            $pattern = '/([a-z]+?)Controller/i';
            $matches = array();
            $controller = $this->getCurrentRequest()->get('_controller');
            preg_match($pattern, $controller, $matches);
            
            $this->controller = isset($matches[1]) ? $matches[1] : $controller;
        }

        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        if (!$this->action) {
            $pattern = "/([a-z]+?)Action/i";
            $matches = array();
            preg_match($pattern, $this->getCurrentRequest()->get('_controller'), $matches);

            $this->action = isset($matches[1]) ? $matches[1] : null;
        }

        return $this->action;
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @return string
     */
    public function generateUrl($name, array $parameters = array(), $absolute = false)
    {
        return $this->container->get('router.url_generator')->generate($name, $parameters, $absolute);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getCurrentRequest()
    {
        if (!$this->request) {
            if ($this->container->isScopeActive('request')) {
                $this->request = $this->container->get('request');
            } else {
                throw new \RuntimeException('Unable to get "request" service');
            }
        }

        return $this->request;
    }
    
    /**
     * Returns the name of the extension
     *
     * @return string
     */
    public function getName()
    {
        return 'router';
    }
}
