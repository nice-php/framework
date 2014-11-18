<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Security\Authenticator;

use Nice\Security\AuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;

class SimpleAuthenticator implements AuthenticatorInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns true if the given Request meets authentication requirements
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authenticate(Request $request)
    {
        return $request->get('username') === $this->username
            && $request->get('password') === $this->password;
    }
}
