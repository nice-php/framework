<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\Router\UrlGenerator;

interface DataGeneratorInterface
{
    /**
     * Get formatted route data for use by a URL generator
     *
     * @return array
     */
    public function getData();
}
