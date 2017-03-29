<?php

declare(strict_types=1);

namespace Benrowe\Fqcn;

use Benrowe\Fqcn\Value\Psr4Namespace;
use Composer\Autoload\ClassLoader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

/**
 * Resolver
 * Resolve a php psr4 namespace to a directory
 *
 * @package Benrowe\Fqcn
 */
class Resolver
{
    /**
     * Instance of composer, since this will be used to load the ps4 prefixes
     *
     * @var ClassLoader
     */
    private $composer;

    /**
     * @var Psr4Namespace
     */
    private $namespace;

    /**
     * Resolver constructor.
     *
     * @param Psr4Namespace|string $namespace
     * @param ClassLoader $composer
     */
    public function __construct($namespace, ClassLoader $composer)
    {
        $this->setNamespace($namespace);
        $this->composer = $composer;
    }

    /**
     * Set the namespace to resolve
     *
     * @param Psr4Namespace|string $namespace $namespace
     */
    public function setNamespace($namespace)
    {
        if (!($namespace instanceof Psr4Namespace)) {
            $namespace = new Psr4Namespace($namespace);
        }
        $this->namespace = $namespace;
    }

    /**
     * Get the current namespace
     *
     * @return Psr4Namespace
     */
    public function getNamespace(): Psr4Namespace
    {
        return $this->namespace;
    }

    /**
     * Find all of the avaiable classes under a specific namespace
     *
     * @param  string $instanceOf optional, restrict the classes found to those
     *                            that extend from this base
     * @return array a list of FQCN's that match
     */
    public function findClasses(string $instanceOf = null): array
    {
        $availablePaths = $this->resolveDirectory();

        $constructs = $this->findNamespacedConstuctsInDirectories($availablePaths, $this->namespace);

        // apply filtering
        if ($instanceOf !== null) {
            $constructs = array_values(array_filter($constructs, function ($constructName) use ($instanceOf) {
                return is_subclass_of($constructName, $instanceOf);
            }));
        }

        return $constructs;
    }

    /**
     * Resolve a psr4 based namespace to a list of absolute directory paths
     *
     * @return array list of directories this namespace is mapped to
     * @throws Exception
     */
    public function resolveDirectory(): array
    {
        $prefixes = $this->composer->getPrefixesPsr4();
        // pluck the best namespace from the available
        $namespacePrefix   = $this->findNamespacePrefix($this->namespace, array_keys($prefixes));
        if (!$namespacePrefix) {
            throw new Exception('Could not find registered psr4 prefix that matches '.$this->namespace);
        }

        return $this->buildDirectoryList($prefixes[$namespacePrefix], $this->namespace, $namespacePrefix);
    }

    /**
     * Build a list of absolute paths, for the given namespace, based on the relative $prefix
     *
     * @param  array  $directories the list of directories (their position relates to $prefix)
     * @param  Psr4Namespace $namespace   The base namespace
     * @param  string $prefix      The psr4 namespace related to the list of provided directories
     * @return array directory paths for provided namespace
     */
    private function buildDirectoryList(array $directories, Psr4Namespace $namespace, string $prefix): array
    {
        $discovered = [];
        foreach ($directories as $path) {
            $path = $this->findAbsolutePathForPsr4($namespace, $prefix, $path);
            // convert the rest of the relative path, from the prefix into a directory slug
            if ($path && is_dir($path)) {
                $discovered[] =  $path;
            }
        }
        return $discovered;
    }

    /**
     * Find the best psr4 namespace prefix, based on the supplied namespace, and
     * list of provided prefix
     *
     * @param Psr4Namespace $namespace
     * @param array  $namespacePrefixes
     * @return string
     */
    private function findNamespacePrefix(Psr4Namespace $namespace, array $namespacePrefixes): string
    {
        $prefixResult = '';

        // find the best matching prefix!
        foreach ($namespacePrefixes as $prefix) {
            // if we have a match, and it's longer than the previous match
            if (substr($namespace->getValue(), 0, strlen($prefix)) == $prefix &&
                strlen($prefix) > strlen($prefixResult)
            ) {
                $prefixResult = $prefix;
            }
        }
        return $prefixResult;
    }

    /**
     * Get an absolute path for the provided namespace, based on a existing
     * directory and its psr4 prefix
     *
     * @param string $namespace
     * @param string $psr4Prefix the psr4 prefix
     * @param string $psr4Path and it's related path
     * @return string the absolute directory path the provided namespace, given
     *                    the correct prefix and path empty string if path can't
     *                    be resolved
     */
    private function findAbsolutePathForPsr4(Psr4Namespace $namespace, string $psr4Prefix, string $psr4Path): string
    {
        // calculate the diff between the entire namespace and the prefix
        // this will translate into a directory map based on the psr4 standard
        $relFqn = trim(substr($namespace->getValue(), strlen($psr4Prefix)), '\\/');
        $path =
            $psr4Path .
            DIRECTORY_SEPARATOR .
            strtr($relFqn, [
                '\\' => DIRECTORY_SEPARATOR,
                '//' => DIRECTORY_SEPARATOR
            ]);
        $path = realpath($path);

        return $path ?: '';
    }


    /**
     * Retrieve a directory iterator for the supplied path
     *
     * @param  string $path The directory to iterate
     * @return RegexIterator
     */
    private function getDirectoryIterator(string $path): RegexIterator
    {
        $dirIterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($dirIterator);
        return new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    }

    /**
     * Determine if the construct (class, interface or trait) exists
     *
     * @param string $artifactName
     * @return bool
     */
    private function langaugeConstructExists(string $artifactName): bool
    {
        return
            $this->checkConstructExists($artifactName, false) ||
            $this->checkConstructExists($artifactName);
    }

    /**
     * Determine if the contract exists
     *
     * @param  string $artifactName
     * @param  bool $autoload trigger the autoloader to be fired, if the construct
     *                        doesn't exist
     * @return bool
     */
    private function checkConstructExists(string $artifactName, bool $autoload = true): bool
    {
        return
            class_exists($artifactName, $autoload) ||
            interface_exists($artifactName, $autoload) ||
            trait_exists($artifactName, $autoload);
    }

    /**
     * Process a list of directories, searching for langauge constructs (classes,
     * interfaces, traits) that exist in them, based on the supplied base
     * namespace
     *
     * @param  array  $directories list of absolute directory paths
     * @param  Psr4Namespace $namespace   The namespace these directories are representing
     * @return array
     */
    private function findNamespacedConstuctsInDirectories(array $directories, Psr4Namespace $namespace): array
    {
        $constructs = [];
        foreach ($directories as $path) {
            $constructs = array_merge($constructs, $this->findNamespacedConstuctsInDirectory($path, $namespace));
        }

        sort($constructs);

        return $constructs;
    }

    /**
     * Recurisvely scan the supplied directory for langauge constructs that are
     * $namespaced
     *
     * @param  string $directory The directory to scan
     * @param  Psr4Namespace $namespace the namespace that represents this directory
     * @return array
     */
    private function findNamespacedConstuctsInDirectory(string $directory, Psr4Namespace $namespace): array
    {
        $constructs = [];

        foreach ($this->getDirectoryIterator($directory) as $file) {
            $fqcn = $namespace.strtr(substr($file[0], strlen($directory) + 1, -4), '//', '\\');
            if ($this->langaugeConstructExists($fqcn)) {
                $constructs[] = $fqcn;
            }
        }

        return $constructs;
    }
}
