<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Security;

use Symfony\Component\HttpFoundation\Request;

interface AuthenticatorInterface
{
    /**
     * Returns true if the given Request meets authentication requirements
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authenticate(Request $request);
}
