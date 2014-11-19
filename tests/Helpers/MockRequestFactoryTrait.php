<?php

namespace Nice\Tests\Helpers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

trait MockRequestFactoryTrait
{
    /**
     * @param string $url
     * @param string $method
     * @param bool   $session
     *
     * @return Request
     */
    protected function createRequest($url, $method = 'GET', $session = true)
    {
        $request = Request::create($url, $method);

        if ($session) {
            $request->setSession(new Session(new MockArraySessionStorage()));
        }

        return $request;
    }
}
