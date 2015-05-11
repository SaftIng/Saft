<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Test\TestCase;

class AnyPatternImplTest extends TestCase
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
