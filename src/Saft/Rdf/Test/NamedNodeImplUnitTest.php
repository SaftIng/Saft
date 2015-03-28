<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\NamedNodeImpl;

class NamedNodeImplUnitTest extends NamedNodeAbstractTest
{
    /**
     * Return a new instance of NamedNodeImpl
     */
    public function newInstance($uri)
    {
        return new NamedNodeImpl($uri);
    }

    /**
     * Tests check
     * What is this check for? There shouldn't be a method check
     */
    public function testCheck()
    {
        $fixture = $this->newInstance('http://saft/test');
        $this->assertFalse($fixture->check(''));
        $this->assertFalse($fixture->check('http//foobar/'));

        $this->assertTrue($fixture->check('http:foobar/'));
        $this->assertTrue($fixture->check('http://foobar/'));
        $this->assertTrue($fixture->check('http://foobar:42/'));
        $this->assertTrue($fixture->check('http://foo:bar@foobar/'));
    }
}
