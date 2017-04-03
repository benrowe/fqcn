<?php

declare(strict_types=1);

namespace Benrowe\Fqcn\Value;

use Benrowe\Fqcn\Exception;

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
        if (!$this->isValid($value)) {
            throw new Exception('Invalid Namespace');
        }
        $this->namespace = $value . '\\';
    }

    /**
     * Determine if the supplied namespace string is a valid according to the
     * psr4 standard
     *
     * @param  string  $namespace
     * @return boolean
     */
    private function isValid(string $namespace): bool
    {
        $parts = explode('\\', $namespace);
        $verified = array_filter($parts, function ($value) {
            return (bool)preg_match("/^[A-Z][\w\d_]+$/", $value);
        });
        return count($parts) === count($verified);
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
        return $value->getValue() === $this->getValue();
    }

    /**
     * Determine if this namepsace starts with the supplied namespace
     *
     * @param  Psr4Namespace $value
     * @return bool
     */
    public function startsWith(Psr4Namespace $value): bool
    {
        $start = substr($this->getValue(), 0, strlen($value->getValue()));

        return $start === $value->getValue();
    }
}
