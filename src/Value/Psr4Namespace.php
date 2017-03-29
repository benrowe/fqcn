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

    /**
     * Constructor
     * Take in the namespace value as a string
     *
     * @param string $namespace
     */
    public function __construct(string $namespace)
    {
        $this->setValue($namespace);
    }

    /**
     * Handle setting the namepsace string value
     *
     * @param string $value
     */
    private function setValue(string $value)
    {
        // standardise the value
        $value = trim($value, '\\');
        // validate the namespace!
        if (!$value) {
            throw new Exception('Invalid Namespace');
        }
        $this->namespace = $value . '\\';
    }

    /**
     * Get the namespace value as a string
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->namespace;
    }

    /**
     * Handle the object when treated as a string
     *
     * @return string
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
