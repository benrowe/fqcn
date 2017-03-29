<?php

namespace Benrowe\Fqcn;

use Benrowe\Fqcn\Value\Psr4Namespace;
use Benrowe\Fqcn\Resolver;

/**
 * Class Factory Test
 *
 * @package Benrowe\Fqcn\Tests\Unit
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $composer = require './vendor/autoload.php';
        $factory = new Factory($composer);
        $this->assertInstanceOf(Resolver::class, $factory->make('Namespace\\To\\Test'));
    }
}
