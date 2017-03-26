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
     * @var array
     */
    private $extensions = ['.php'];

    /**
     * Resolver constructor.
     *
     * @param ClassLoader $composer
     * @param array       $extensions
     */
    public function __construct(ClassLoader $composer, $extensions = null)
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
        $availablePaths = $this->resolveDirectory($namespace);

        $classes = [];
        foreach ($availablePaths as $path) {
            foreach ($this->getDirectoryIterator($path) as $file) {
                $fqcn = $namespace.strtr(substr($file[0], strlen($path), -4), '//', '\\');
                try {
                    // test if the class exists
                    new \ReflectionClass($fqcn);
                    $classes[] = $fqcn;
                } catch (\ReflectionException $e) {
                    // could not load the class/interface/trait
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
     * @param string $namespace
     * @param array  $prefixes
     * @return string
     */
    private function findPrefix(string $namespace, array $prefixes): string
    {
        $prefixResult = '';

        // find the best matching prefix!
        foreach ($prefixes as $prefix) {
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
     * Get an absolute path for the provided namespace, based on a existing directory and its psr4 prefix
     *
     * @param string $namespace
     * @param string $psr4Prefix the psr4 prefix
     * @param string $psr4Path and it's related path
     * @return string the absolute directory path the provided namespace, given the correct prefix and path
     *                empty string if path cant be resolved
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
}
