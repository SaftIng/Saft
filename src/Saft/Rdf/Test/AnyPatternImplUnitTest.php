<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\AnyPatternImpl;

class AnyPatternImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * An abstract method which returns new instances of BlankNode
     */
    public function newInstance()
    {
        return new AnyPatternImpl();
    }

    final public function testIsBlank()
    {
        $fixtureA = $this->newInstance();

        $this->assertFalse($fixtureA->isBlank());
    }
}
