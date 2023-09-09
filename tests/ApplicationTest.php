<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Tests;

use PHPUnit\Framework\TestCase;
use Nice\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class ApplicationTest extends TestCase
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
        $this->assertEquals($rootDir.'/cache/test', $app->getCacheDir());
        $this->assertEquals($rootDir.'/logs', $app->getLogDir());
        $this->assertEquals('UTF-8', $app->getCharset());
        $this->assertTrue($app->isCacheEnabled());
    }

    /**
     * Test ExtendableInterface methods
     */
    public function testExtensionMethods()
    {
        $app = $this->getMockApplication();

        $middleExtension = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\Extension\ExtensionInterface');
        $app->appendExtension($middleExtension);

        $prependedExtension = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\Extension\ExtensionInterface');
        $app->prependExtension($prependedExtension);

        $appendedExtension = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\Extension\ExtensionInterface');
        $app->appendExtension($appendedExtension);

        $extensions = $app->getExtensions();

        $this->assertCount(3, $extensions);
        $this->assertSame($prependedExtension, $extensions[0]);
        $this->assertSame($middleExtension, $extensions[1]);
        $this->assertSame($appendedExtension, $extensions[2]);
    }

    /**
     * Test a disabled cache
     */
    public function testDisabledCache()
    {
        $app = $this->getMockApplication(null, false);

        $this->assertNull($app->getCacheDir());
        $this->assertFalse($app->isCacheEnabled());
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
            'get'           => array(array('test'), 'Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException'),
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
     * Test configuration provider methods
     */
    public function testConfigurationProvider()
    {
        $mockProvider = $this->getMockForAbstractClass('Nice\DependencyInjection\ConfigurationProviderInterface');

        $app = new Application();
        $app->setConfigurationProvider($mockProvider);

        $this->assertSame($mockProvider, $app->getConfigurationProvider());
    }

    /**
     * Test getRootDir method
     */
    public function testGetRootDir()
    {
        $expectedRootDir = __DIR__.'/..';

        $app = new ExtendedApplication();

        $this->assertEquals($expectedRootDir, $app->getRootDir());
    }

    /**
     * Test getRootDir method when installed with composer
     */
    public function testGetRootDirVendor()
    {
        require_once __DIR__.'/Mocks/vendor/nice/framework/src/TestApplication.php';

        $expectedRootDir = __DIR__.'/Mocks';

        $app = new \TestApplication();

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
        $mockKernel = $this->getMockForAbstractClass('Nice\Tests\TerminableHttpKernelInterface');
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
     * Test default Application extensions
     */
    public function testDefaultExtensions()
    {
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->onlyMethods(array('getRootDir'))
            ->setConstructorArgs(array('init', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(sys_get_temp_dir()));

        $app->boot();

        $extensions = $app->getExtensions();
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf('Nice\Extension\RouterExtension', $extensions[0]);
    }

    /**
     * Test container initialization
     */
    public function testInitializeContainer()
    {
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->onlyMethods(array('getRootDir'))
            ->setConstructorArgs(array('init', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(sys_get_temp_dir()));

        $app->boot();

        $container = $app->getContainer();
        $this->assertNotNull($container);

        $app->boot();
        $this->assertSame($container, $app->getContainer());
    }

    /**
     * Test failure to create cache directory
     */
    public function testFailureToCreateCacheDir()
    {
        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->onlyMethods(array('getRootDir', 'getCacheDir'))
            ->setConstructorArgs(array('create', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue('/someunwriteable/path'));

        $this->expectException('RuntimeException', 'Unable to create the cache directory');

        $app->boot();
    }

    /**
     * Test failure to write to cache directory
     */
    public function testFailureToWriteCacheDir()
    {
        $tmpdir = sys_get_temp_dir().'/'.md5(uniqid());
        mkdir($tmpdir, 0700, true);
        chmod($tmpdir, 0000);

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->onlyMethods(array('getRootDir', 'getCacheDir'))
            ->setConstructorArgs(array('write', true))
            ->getMock();
        $app->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue($tmpdir));

        $this->expectException('RuntimeException', 'Unable to write in the cache directory');

        $app->boot();
    }

    /**
     * @param HttpKernelInterface $kernel
     *
     * @return Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockApplication(HttpKernelInterface $kernel = null, $cache = true)
    {
        $kernel = $kernel ?: $this->getMockForAbstractClass('Symfony\Component\HttpKernel\HttpKernelInterface');

        /** @var \Nice\Application|\PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMockBuilder('Nice\Application')
            ->onlyMethods(array('getRootDir', 'registerDefaultExtensions', 'initializeContainer'))
            ->setConstructorArgs(array('test', true, $cache))
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

interface TerminableHttpKernelInterface extends HttpKernelInterface, TerminableInterface
{
}

class ExtendedApplication extends Application
{
}
