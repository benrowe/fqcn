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
}
