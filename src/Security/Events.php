<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Security;

final class Events
{
    const LOGIN_SUCCESS = 'security.login_success';

    const LOGIN_FAIL = 'security.login_fail';

    const LOGOUT = 'security.logout';
}
