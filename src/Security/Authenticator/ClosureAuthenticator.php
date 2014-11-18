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

class ClosureAuthenticator implements AuthenticatorInterface
{
    /**
     * @var callable
     */
    private $closure;

    /**
     * Constructor
     *
     * @param callable $closure Not necessarily a closure
     */
    public function __construct(callable $closure)
    {
        $this->closure = $closure;
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
        return (bool) call_user_func($this->closure, $request);
    }
}
