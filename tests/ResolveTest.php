<?php

namespace Benrowe\Fqcn;

use Benrowe\Fqcn\Value\Psr4Namespace;
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
        $this->resolve = new Resolver('Example\\Namespace\\', $composer);
    }

    public function testGet()
    {
        $tmp = new Psr4Namespace('Example\\Namespace');
        $this->assertTrue($tmp->equals($this->resolve->getNamespace()));
    }

    public function testSet()
    {
        $tmp = new Psr4Namespace('Hello');
        $this->resolve->setNamespace($tmp);
        $this->assertTrue($tmp->equals($this->resolve->getNamespace()));

        $this->resolve->setNamespace('Hello\\');
        $this->assertTrue($tmp->equals($this->resolve->getNamespace()));
    }

    public function testResolve()
    {
        $this->resolve->setNamespace('\Benrowe\Fqcn\Test');
        $this->assertSame([__DIR__.DIRECTORY_SEPARATOR.'Test'], $this->resolve->findDirectories());
    }

    public function testResolveDoesntExist()
    {
        $this->resolve->setNamespace('\Benrowe\Fqcn\Madeup');
        $this->assertSame([], $this->resolve->findDirectories());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Could not find registered psr4 prefix that matches ThisDoesNotExist\
     */
    public function testUnknownResolve()
    {
        $this->resolve->setNamespace('\ThisDoesNotExist\\');
        $this->assertSame(__DIR__, $this->resolve->findDirectories());
    }

    /**
     * @expectedException Exception
     * @dataProvider dataInvalidNamespace
     * @param $namespace
     */
    public function testInvalidNamespace($namespace)
    {
        $this->resolve->setNamespace($namespace);
    }

    public function testfindConstructs()
    {
        $this->resolve->setNamespace(__NAMESPACE__.'\\Test');
        $this->assertSame([
            'Benrowe\Fqcn\Test\Base',
            'Benrowe\Fqcn\Test\ExampleBase',
            'Benrowe\Fqcn\Test\Example\AnotherTrait',
            'Benrowe\Fqcn\Test\Example\ExampleBase',
            'Benrowe\Fqcn\Test\Example\SomeInterface',
            'Benrowe\Fqcn\Test\Standalone',
        ], $this->resolve->findConstructs());
    }

    public function testFindClassesInstanceOf()
    {
        $this->resolve->setNamespace(__NAMESPACE__.'\\Test');
        $this->assertSame([
            'Benrowe\Fqcn\Test\ExampleBase',
            'Benrowe\Fqcn\Test\Example\ExampleBase',
        ], $this->resolve->findConstructs(__NAMESPACE__.'\\Test\\Base'));
    }

    public function dataInvalidNamespace()
    {
        return [
            'empty' => [''],
            'slashes' => ['\\'],
        ];
    }
}
