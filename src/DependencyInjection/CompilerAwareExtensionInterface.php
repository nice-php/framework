<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Defines the contract any CompilerAwareExtensionInterface must implement
 *
 * A CompilerAwareExtension is an extension that also requires registration of
 * custom compiler passes.
 */
interface CompilerAwareExtensionInterface
{
    /**
     * Gets the CompilerPasses this extension requires.
     *
     * @return array|CompilerPassInterface[]
     */
    public function getCompilerPasses();
}
