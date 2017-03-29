<?php

declare(strict_types=1);

namespace Benrowe\Fqcn\Value;

/**
 * This represents a psr4 based namespace
 *
 * @package Benrowe\Fqcn
 */
final class Psr4Namespace
{
    private $namespace;

    public function __construct(string $namespace)
    {
        $this->setValue($namespace);
    }

    private function setValue($value)
    {
        $value = trim($value, '\\');
        if (!$value) {
            throw new Exception('Invalid Namespace');
        }
        $this->namespace = $value . '\\';
    }

    public function getValue(): string
    {
        return $this->namespace;
    }

    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    /**
     * Determine the supplied namespace is the same as the current namespace
     *
     * @param  Psr4Namespace $value
     * @return bool
     */
    public function equals(Psr4Namespace $value): bool
    {
        if ($value instanceof $this) {
            return $value->getValue() === $this->getValue();
        }
        return $value === $this->getValue();
    }
}
