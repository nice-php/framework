<?php

namespace Nice\Router\UrlGenerator;

interface DataSourceInterface
{
    /**
     * Get formatted route data for use by a URL generator
     *
     * @return array
     */
    public function getData();
}