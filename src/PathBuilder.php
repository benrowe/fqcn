<?php

declare(strict_types=1);

namespace Benrowe\Fqcn;

use Benrowe\Fqcn\Value\Psr4Namespace;

/**
 * PathBuilder
 *
 * Build a path based on a namespace and the directory it's mapped to
 *
 * @package Benrowe\Fqcn
 */
class PathBuilder
{
    private $namespace;
    private $path;

    /**
     * Constructor
     * Take in the path & the psr4 namespace it represents
     * @param string        $path      [description]
     * @param Psr4Namespace $namespace [description]
     */
    public function __construct(string $path, Psr4Namespace $namespace)
    {
        if (!is_dir($path)) {
            throw new Exception('Invalid path');
        }
        $this->namespace = $namespace;
        $this->path = $path;
    }

    public function resolve(Psr4Namespace $namespace): string
    {
        if (!$namespace->startsWith($this->namespace)) {
            throw new Exception($namespace->getValue() . ' is not from the same base as ' . $this->namespace->getValue());
        }
        $relFqn = substr($namespace->getValue(), strlen($this->namespace->getValue()));
        $relPath = $this->nsToPath($relFqn);

        $absPath = realpath($this->path . DIRECTORY_SEPARATOR . $relPath);

        return $absPath ?: '';
    }

    private function nsToPath(string $namespace): string
    {
        return trim(strtr($namespace, [
            '\\' => DIRECTORY_SEPARATOR,
            '//' => DIRECTORY_SEPARATOR
        ]), '\\/');
    }
}
