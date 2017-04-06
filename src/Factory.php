<?php

declare(strict_types=1);

namespace Benrowe\Fqcn;

use Composer\Autoload\ClassLoader;

/**
 * Resolver Factory
 *
 * Creates instances of the Resolver clas
 *
 * @package Benrowe\Fqcn
 */
class Factory
{
    /**
     * @var ClassLoader
     */
    private $composer;

    /**
     * Factory Constructor
     *
     * @param ClassLoader $composer [description]
     */
    public function __construct(ClassLoader $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Make an instance of the Resolver object using the provided namespace as a
     * string
     *
     * @param  string $namespace
     * @return Resolver
     */
    public function make(string $namespace): Resolver
    {
        return new Resolver($namespace, $this->composer);
    }
}
