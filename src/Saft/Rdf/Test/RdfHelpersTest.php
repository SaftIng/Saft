<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Rdf\Test;

use Saft\Rdf\RdfHelpers;

class RdfHelpersTest extends TestCase
{
    protected $rdfHelpers;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = new RdfHelpers();
    }

    /*
     * Tests for encodeStringLitralForNQuads
     */

    public function testEncodeStringLitralForNQuads()
    {
        $this->assertEquals('\\\\', $this->fixture->encodeStringLiteralForNQuads('\\'));
        $this->assertEquals('\t', $this->fixture->encodeStringLiteralForNQuads("\t"));
        $this->assertEquals('\r', $this->fixture->encodeStringLiteralForNQuads("\r"));
        $this->assertEquals('\n', $this->fixture->encodeStringLiteralForNQuads("\n"));
        $this->assertEquals('\"', $this->fixture->encodeStringLiteralForNQuads('"'));
    }

    /*
     * Tests for guessFormat
     */

    public function testGuessFormat()
    {
        // n-triples
        $this->assertEquals('n-triples', $this->fixture->guessFormat('<foo><bar>'));
        $this->assertEquals('n-triples', $this->fixture->guessFormat('<foo>'));

        // rdf xml
        $this->assertEquals('rdf-xml', $this->fixture->guessFormat('<rdf:aa'));

        // turtle
        $this->assertEquals('turtle', $this->fixture->guessFormat('@prefix foo:<http://bar>'));

        // invalid strings
        $this->assertEquals(null, $this->fixture->guessFormat('<foo'));
        $this->assertEquals(null, $this->fixture->guessFormat('foo://>'));
    }

    // test that guessFormat doesn't confuse n-triples with turtle
    public function testGuessFormatRegression1()
    {
        $fileContent = file_get_contents(__DIR__.'/resources/guessFormat-regression1.ttl');

        $this->assertEquals('turtle', $this->fixture->guessFormat($fileContent));
    }

    public function testGuessFormatUnknownLeadsToNull()
    {
        $this->assertNull($this->fixture->guessFormat(1));
        $this->assertNull($this->fixture->guessFormat(true));
    }

    /*
     * Tests for simpleCheckBlankNodeId
     */

    public function testSimpleCheckBlankNodeId()
    {
        $this->assertFalse($this->fixture->simpleCheckBlankNodeId('_a'));
        $this->assertFalse($this->fixture->simpleCheckBlankNodeId(':aaa'));

        $this->assertTrue($this->fixture->simpleCheckBlankNodeId('_:aaa'));
        $this->assertTrue($this->fixture->simpleCheckBlankNodeId('_:aaa/_aa'));
    }

    /*
     * Tests for simpleCheckURI
     */

    public function testSimpleCheckURI()
    {
        $this->assertFalse($this->fixture->simpleCheckURI(''));
        $this->assertFalse($this->fixture->simpleCheckURI('http//foobar/'));

        $this->assertTrue($this->fixture->simpleCheckURI('http:foobar/'));
        $this->assertTrue($this->fixture->simpleCheckURI('http://foobar/'));
        $this->assertTrue($this->fixture->simpleCheckURI('http://foobar:42/'));
        $this->assertTrue($this->fixture->simpleCheckURI('http://foo:bar@foobar/'));
    }
}
