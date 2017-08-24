<?php

namespace Benrowe\Fqcn;

use Benrowe\Fqcn\PathBuilder;
use Benrowe\Fqcn\Value\Psr4Namespace;

/**
 * Class PathBuilder Test
 *
 * @package Benrowe\Fqcn\Tests\Unit
 */
class PathBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespacePath()
    {
        $namespace = new Psr4Namespace(__NAMESPACE__.'\\Test');
        $builder = new PathBuilder(__DIR__, new Psr4Namespace(__NAMESPACE__));
        $this->assertSame(realpath(__DIR__.DIRECTORY_SEPARATOR.'Test'), $builder->resolve($namespace));
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPath()
    {
        $builder = new PathBuilder(__DIR__.DIRECTORY_SEPARATOR.'Madeup', new Psr4Namespace(__NAMESPACE__));
    }

    /**
     * @expectedException Exception
     */
    public function testResolveInvalid()
    {
        $namespace = new Psr4Namespace('DoesNotExist');
        $builder = new PathBuilder(__DIR__, new Psr4Namespace(__NAMESPACE__));
        $this->assertSame(realpath(__DIR__.DIRECTORY_SEPARATOR.'Test'), $builder->resolve($namespace));
    }
}
