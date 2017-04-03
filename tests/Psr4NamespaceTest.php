<?php

namespace Benrowe\Fqcn;

use Benrowe\Fqcn\Value\Psr4Namespace;

/**
 * Class Psr4Namespace Test
 *
 * @package Benrowe\Fqcn\Tests\Unit
 */
class Psr4NamespaceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {

        $namespace = new Psr4Namespace('\\Something\\To\\Do\\');
        $this->assertSame('Something\To\Do\\', $namespace->getValue());
        $this->assertSame('Something\To\Do\\', (string)$namespace);
    }

    public function testEquals()
    {
        $namespace = new Psr4Namespace('\\Something\\Haha\\Other\\');
        $this->assertTrue($namespace->equals(new Psr4Namespace('Something\\Haha\\Other')));
    }

    public function testStartsWith()
    {
        $namespace = new Psr4Namespace('\\Something\\Haha\\Other\\');
        $this->assertTrue($namespace->startsWith(new Psr4Namespace('Something\\')));
        $this->assertTrue($namespace->startsWith(new Psr4Namespace('Something\\Haha')));
        $this->assertFalse($namespace->startsWith(new Psr4Namespace('SomethingElse')));
    }

    /**
     * @dataProvider dataInvalidNamespace
     * @expectedException \Benrowe\Fqcn\Exception
     *
     * @param  string $namespace
     * @return void
     */
    public function testInvalidData($namespace)
    {
        $namespace = new Psr4Namespace($namespace);
    }

    public function dataInvalidNamespace()
    {
        return [
            'empty' => [''],
            'invalid-chars' => ['something-cool/haha'],
            'numbers' => ['4Something'],
            'hack' => ['\\\\\\']
        ];
    }
}
