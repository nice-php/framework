<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests;

use Nice\Application;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the instantiation
     */
    public function testInstantiation()
    {
        $app = $this->getMockApplication();
        
        $this->assertTrue($app->isDebug());
        $this->assertEquals('test', $app->getEnvironment());

        $rootDir = $app->getRootDir();
        $this->assertEquals(sys_get_temp_dir(), $rootDir);
        $this->assertEquals($rootDir . '/cache/test', $app->getCacheDir());
        $this->assertEquals($rootDir . '/logs', $app->getLogDir());
        $this->assertEquals('UTF-8', $app->getCharset());
    }

    /**
     * Test that the container methods cause the Application to boot
     */
    public function testContainerMethodsCauseBoot()
    {
        /**
         * array(
         *   'methodName' => array(array $args, string expectedException)
         * )
         */
        $methods = array(
            'set'           => array(array('test', new \stdClass())),
            'has'           => array(array('test')),
            'hasParameter'  => array(array('test')),
            'addScope'      => array(array(new Scope('test'))),
            'hasScope'      => array(array('test')),
            'isScopeActive' => array(array('test')),
            'get'           => array(array('test'), 'Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException'),
            'getParameter'  => array(array('test'), 'Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException'),
            'setParameter'  => array(array('test', 'value'), 'Symfony\Component\DependencyInjection\Exception\LogicException'),
            'enterScope'    => array(array('test'), 'Symfony\Component\DependencyInjection\Exception\InvalidArgumentException'),
            'leaveScope'    => array(array('test'), 'Symfony\Component\DependencyInjection\Exception\InvalidArgumentException'),
        );

        foreach ($methods as $method => $parts) {
            $app = $this->getMockApplication();

            $this->assertNull($app->getContainer());

            try {
                call_user_func_array(array($app, $method), $parts[0]);

            } catch (\Exception $e) {
                if (isset($parts[1])) {
                    $this->assertEquals($parts[1], get_class($e));

                } else {
                    throw $e;
                }
            }

            $this->assertNotNull($app->getContainer());
        }
    }

    /**
     * Test entering and leaving scope
     */
    public function testEnterAndLeaveScope()
    {
        $app = $this->getMockApplication();
        
        $app->boot();
        
        $app->addScope(new Scope('test'));
        
        $this->assertTrue($app->hasScope('test'));
        $this->assertFalse($app->isScopeActive('test'));
        
        $app->enterScope('test');
        
        $this->assertTrue($app->isScopeActive('test'));
        
        $app->leaveScope('test');
        $this->assertFalse($app->isScopeActive('test'));
    }

    /**
     * @backupGlobals
     *
     * Test getRootDir method
     */
    public function testGetRootDir()
    {
        $_SERVER['SCRIPT_FILENAME'] = tempnam(null, 'scopeTest');

        $expectedRootDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/..';

        $app = new Application();

        $this->assertEquals($expectedRootDir, $app->getRootDir());
    }

    /**
     * Test the handle method
     */
    public function testHandle()
    {
        $expectedResponse = new Response();
        $mockKernel = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\HttpKernelInterface');
        $mockKernel->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($expectedResponse));

        $app = $this->getMockApplication($mockKernel);

        $request = Request::create('/test', 'GET');

        $response = $app->handle($request);

        $this->assertSame($expectedResponse, $response);
    }

    /**
     * Test the run method
     */
    public function testRun()
    {
        $mockKernel = $this->getMockForAbstractClass('Nice\Tests\TerminalHttpKernelInterface');
        $mockKernel->expects($this->once())
            ->method('handle')
            ->will($this->returnValue(new Response()));
        $mockKernel->expects($this->once())
            ->method('terminate');

        $app = $this->getMockApplication($mockKernel);

        $request = Request::create('/test', 'GET');

        $app->run($request);
    }

    /**
     * Test container initialization
     */
    public function testInitializeContainer()
    {
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('getRootDir'))
            ->setConstructorArgs(array('init', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(sys_get_temp_dir()));
        
        $extensions = $app->getRegisteredExtensions();
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf('Nice\Extension\RouterExtension', $extensions[0]);
        $app->boot();
        
        $container = $app->getContainer();
        $this->assertNotNull($container);
        
        $app->boot();
        $this->assertSame($container, $app->getContainer());
    }

    /**
     * Test registration of custom container configurations
     */
    public function testRegisterContainerConfiguration()
    {
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Tests\SimpleApplication')
            ->setMethods(array('getRootDir'))
            ->setConstructorArgs(array('register', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(sys_get_temp_dir()));
        $app->boot();
        
        $container = $app->getContainer();
        
        $this->assertTrue($container->has('test'));
    }

    /**
     * Test failure to create cache directory
     */
    public function testFailureToCreateCacheDir()
    {
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('getRootDir', 'getCacheDir'))
            ->setConstructorArgs(array('create', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue('/someunwriteable/path'));
        
        $this->setExpectedException('RuntimeException', 'Unable to create the cache directory');
        
        $app->boot();
    }

    /**
     * Test failure to write to cache directory
     */
    public function testFailureToWriteCacheDir()
    {
        $tmpdir = sys_get_temp_dir() . '/' . md5(uniqid());
        mkdir($tmpdir, 0700, true);
        chmod($tmpdir, 0000);
        
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('getRootDir', 'getCacheDir'))
            ->setConstructorArgs(array('write', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue($tmpdir));

        $this->setExpectedException('RuntimeException', 'Unable to write in the cache directory');

        $app->boot();
    }
    
    /**
     * @param HttpKernelInterface $kernel
     *
     * @return Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockApplication(HttpKernelInterface $kernel = null)
    {
        $kernel = $kernel ?: $this->getMockForAbstractClass('Symfony\Component\HttpKernel\HttpKernelInterface');

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->setMethods(array('getRootDir', 'registerDefaultExtensions', 'initializeContainer'))
            ->setConstructorArgs(array('test', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(sys_get_temp_dir()));

        $container = new Container();
        $container->set('http_kernel', $kernel);

        $app->expects($this->any())
            ->method('initializeContainer')
            ->will($this->returnValue($container));

        return $app;
    }
}

interface TerminalHttpKernelInterface extends HttpKernelInterface, TerminableInterface
{

}

class SimpleApplication extends Application
{
    /**
     * Loads the container configuration
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    protected function registerContainerConfiguration(LoaderInterface $loader)
    {
        $container = new ContainerBuilder();
        $container->register('test', 'stdClass');
        
        return $container;
    }
}