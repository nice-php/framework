<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Nice\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Defines the contract any Extendable object must implement
 */
interface ExtendableInterface
{
    /**
     * Prepend an extension
     *
     * @param ExtensionInterface $extension
     */
    public function prependExtension(ExtensionInterface $extension);

    /**
     * Append an extension
     *
     * @param ExtensionInterface $extension
     */
    public function appendExtension(ExtensionInterface $extension);

    /**
     * Get an ordered list of extensions
     *
     * @return array|ExtensionInterface[]
     */
    public function getExtensions();

    /**
     * Adds a compiler pass.
     *
     * @param CompilerPassInterface $pass A compiler pass
     * @param string                $type The type of compiler pass
     */
    public function addCompilerPass(CompilerPassInterface $pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION);
}
