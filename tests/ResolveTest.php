<?php

namespace Benrowe\Fqcn;

use Benrowe\Fqcn\Exception;
use Benrowe\Fqcn\Resolver;

/**
 * Class ResolveTest
 *
 * @package Benrowe\Fqcn\Tests\Unit
 */
class ResolveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resolver
     */
    private $resolve;

    public function setUp()
    {
        $composer = require './vendor/autoload.php';
        $this->resolve = new Resolver($composer);
    }

    public function testResolve()
    {
        $this->assertSame([__DIR__.DIRECTORY_SEPARATOR.'Test'], $this->resolve->resolveDirectory('\Benrowe\Fqcn\Test'));
    }

    public function testResolveDoesntExist()
    {
        $this->assertSame([], $this->resolve->resolveDirectory('\Benrowe\Fqcn\Madeup'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Could not find registered psr4 prefix that matches ThisDoesNotExist\
     */
    public function testUnknownResolve()
    {
        $this->assertSame(__DIR__, $this->resolve->resolveDirectory('\ThisDoesNotExist\\'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 100
     * @dataProvider dataInvalidNamespace
     * @param $namespace
     */
    public function testInvalidNamespace($namespace)
    {
        $this->resolve->resolveDirectory($namespace);
    }

    public function testFindClasses()
    {
        $this->assertSame([
            'Benrowe\Fqcn\Test\Base',
            'Benrowe\Fqcn\Test\ExampleBase',
            'Benrowe\Fqcn\Test\Example\AnotherTrait',
            'Benrowe\Fqcn\Test\Example\ExampleBase',
            'Benrowe\Fqcn\Test\Example\SomeInterface',
            'Benrowe\Fqcn\Test\Standalone',
        ], $this->resolve->findClasses(__NAMESPACE__.'\\Test'));
    }

    public function testFindClassesInstanceOf()
    {
        $this->assertSame([
            'Benrowe\Fqcn\Test\ExampleBase',
            'Benrowe\Fqcn\Test\Example\ExampleBase',
        ], $this->resolve->findClasses(__NAMESPACE__.'\\Test', __NAMESPACE__.'\\Test\\Base'));
    }

    public function dataInvalidNamespace()
    {
        return [
            'empty' => [''],
            'slashes' => ['\\'],
        ];
    }
}
