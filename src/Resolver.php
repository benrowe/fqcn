<?php

declare(strict_types=1);

namespace Benrowe\Fqcn;

use Composer\Autoload\ClassLoader;

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
     * Resolver constructor.
     *
     * @param ClassLoader $composer
     */
    public function __construct(ClassLoader $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Find all of the avaiable classes under a specific namespace
     *
     * @param  string $namespace  The namespace to search for
     * @param  string $instanceOf optional, restrict the classes found to those
     *                            that extend from this base
     * @return array a list of FQCN's that match
     */
    public function findClasses(string $namespace, string $instanceOf = null): array
    {
        $namespace = $this->normalise($namespace);
        $availablePaths = $this->resolveDirectory($namespace);

        $classes = [];
        foreach ($availablePaths as $path) {
            foreach ($this->getDirectoryIterator($path) as $file) {
                $fqcn = $namespace.strtr(substr($file[0], strlen($path) + 1, -4), '//', '\\');
                if ($this->langaugeConstructExists($fqcn)) {
                    $classes[] = $fqcn;   
                }
            }
        }

        sort($classes);

        if ($instanceOf) {
            $classes = array_values(array_filter($classes, function ($className) use ($instanceOf) {
                return is_subclass_of($className, $instanceOf);
            }));
        }

        return $classes;
    }

    /**
     * Resolve a psr4 based namespace to an absolute directory
     *
     * @param string $namespace
     * @return array
     * @throws Exception
     */
    public function resolveDirectory(string $namespace): array
    {
        $namespace = $this->normalise($namespace);

        $prefixes = $this->composer->getPrefixesPsr4();
        $prefix   = $this->findPrefix($namespace, array_keys($prefixes));
        if (!$prefix) {
            throw new Exception('Could not find registered psr4 prefix that matches '.$namespace);
        }

        $discovered = [];
        foreach ($prefixes[$prefix] as $path) {
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
     * @param string $namespace
     * @param array  $prefixes
     * @return string
     */
    private function findPrefix(string $namespace, array $prefixes): string
    {
        $prefixResult = '';

        // find the best matching prefix!
        foreach ($prefixes as $prefix) {
            // if we have a match, and it's longer than the previous match
            if (substr($namespace, 0, strlen($prefix)) == $prefix &&
                strlen($prefix) > strlen($prefixResult)
            ) {
                $prefixResult = $prefix;
            }
        }
        return $prefixResult;
    }

    /**
     * Convert the supplied namespace string into a standard format
     * no prefix, ends with trailing slash
     *
     * Example:
     * Psr4\Prefix\
     * Something\
     *
     * @param string $namespace
     * @return string
     * @throws Exception
     */
    private function normalise(string $namespace): string
    {
        $tidy = trim($namespace, '\\');
        if (!$tidy) {
            throw new Exception('Invalid namespace', 100);
        }

        return $tidy . '\\';
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
    private function findAbsolutePathForPsr4(string $namespace, string $psr4Prefix, string $psr4Path): string
    {
        $relFqn = trim(substr($namespace, strlen($psr4Prefix)), '\\/');
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
     * @return \RegexIterator
     */
    private function getDirectoryIterator(string $path): \RegexIterator
    {
        $dirIterator = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        return new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
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
}
