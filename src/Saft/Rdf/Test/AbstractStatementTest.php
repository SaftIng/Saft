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

use Saft\Rdf\AnyPattern;
use Saft\Rdf\BlankNode;
use Saft\Rdf\Literal;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\Statement;

abstract class AbstractStatementTest extends TestCase
{
    /**
     * @codeCoverageIgnore
     */
    abstract public function getLiteralInstance(string $value, $datatype = null, $lang = null): Literal;

    /**
     * @codeCoverageIgnore
     */
    abstract public function getNamedNodeInstance(string $uri): NamedNode;

    /**
     * @codeCoverageIgnore
     */
    abstract public function getAnyPatternInstance($value): AnyPattern;

    /**
     * @codeCoverageIgnore
     */
    abstract public function getBlankNodeInstance(string $blankId): BlankNode;

    /**
     * @param Node $subject
     * @param Node $predicate
     * @param Node $object
     * @param Node $graph     optional
     *
     * @return Statement
     */
    abstract public function getInstance(Node $subject, Node $predicate, Node $object, $graph = null): Statement;

    /*
     * Tests for equals
     */

    public function testEquals()
    {
        $subjectA = $this->getAnyPatternInstance('foo');
        $subjectB = $this->getNamedNodeInstance('http://example.org/');
        $predicate = $this->getNamedNodeInstance('http://example.org/');
        $object = $this->getNamedNodeInstance('http://example.org/');
        $graphA = $this->getAnyPatternInstance('foo');
        $graphB = $this->getNamedNodeInstance('http://example.org/');

        $fixtureA = $this->getInstance($subjectA, $predicate, $object);
        $fixtureB = $this->getInstance($subjectB, $predicate, $object);
        $fixtureC = $this->getInstance($subjectB, $predicate, $object, $graphA);
        $fixtureD = $this->getInstance($subjectB, $predicate, $object, $graphB);
        $fixtureE = $this->getInstance($subjectA, $predicate, $object);
        $fixtureF = $this->getInstance($subjectB, $predicate, $object, $graphA);

        $this->assertTrue($fixtureA->equals($fixtureA));
        $this->assertTrue($fixtureA->equals($fixtureE));
        $this->assertFalse($fixtureA->equals($fixtureB));
        $this->assertFalse($fixtureA->equals($fixtureC));
        $this->assertFalse($fixtureA->equals($fixtureD));
        $this->assertFalse($fixtureB->equals($fixtureA));
        $this->assertTrue($fixtureB->equals($fixtureB));
        $this->assertFalse($fixtureB->equals($fixtureC));
        $this->assertTrue($fixtureC->equals($fixtureF));
        $this->assertTrue($fixtureF->equals($fixtureC));
    }

    /*
     * Tests for isConcrete
     */

    public function testIsConcrete()
    {
        $subjectA = $this->getAnyPatternInstance('foo');
        $subjectB = $this->getNamedNodeInstance('http://example.org/');
        $predicate = $this->getNamedNodeInstance('http://example.org/');
        $object = $this->getNamedNodeInstance('http://example.org/');
        $graphA = $this->getAnyPatternInstance('foo');
        $graphB = $this->getNamedNodeInstance('http://example.org/');

        $fixtureA = $this->getInstance($subjectA, $predicate, $object);
        $fixtureB = $this->getInstance($subjectB, $predicate, $object);
        $fixtureC = $this->getInstance($subjectB, $predicate, $object, $graphA);
        $fixtureD = $this->getInstance($subjectB, $predicate, $object, $graphB);

        $this->assertFalse($fixtureA->isConcrete());
        $this->assertTrue($fixtureA->isPattern());

        $this->assertTrue($fixtureB->isConcrete());
        $this->assertFalse($fixtureB->isPattern());

        $this->assertFalse($fixtureC->isConcrete());
        $this->assertTrue($fixtureC->isPattern());

        $this->assertTrue($fixtureD->isConcrete());
        $this->assertFalse($fixtureD->isPattern());
    }

    /*
     * Tests for matches
     */

    public function testMatches()
    {
        $subject = $this->getNamedNodeInstance('http://foo.net');
        $predicate = $this->getNamedNodeInstance('http://bar.net');
        $graphA = $this->getNamedNodeInstance('http://example.net');
        $graphB = $this->getNamedNodeInstance('http://other.net');
        $object = $this->getLiteralInstance('baz');
        $fixtureA = $this->getInstance($subject, $predicate, $object);
        $fixtureB = $this->getInstance($subject, $predicate, $object, $graphA);
        $fixtureC = $this->getInstance($subject, $predicate, $object, $graphB);

        $any = $this->getAnyPatternInstance('foo');
        $patternA = $this->getInstance($any, $any, $any);
        $patternB = $this->getInstance($any, $any, $any, $any);
        $patternC = $this->getInstance($any, $any, $any, $graphA);

        // TODO check if this is realy the behavior we want
        $this->assertTrue($patternA->matches($fixtureA));
        $this->assertTrue($patternB->matches($fixtureA));
        $this->assertTrue($patternB->matches($fixtureB));
        $this->assertTrue($patternB->matches($fixtureC));
        $this->assertTrue($patternC->matches($fixtureB));
        $this->assertFalse($patternC->matches($fixtureC));

        $subject = $this->getNamedNodeInstance('http://foo.net');
        $predicate = $this->getNamedNodeInstance('http://bar.net');
        $object = $this->getLiteralInstance('baz');
        $pattern = $this->getInstance($subject, $predicate, $object);
        $this->assertTrue($pattern->matches($fixtureA));

        $subject = $this->getAnyPatternInstance('foo');
        $predicate = $this->getNamedNodeInstance('http://bar.net');
        $object = $this->getAnyPatternInstance('foo');
        $pattern = $this->getInstance($subject, $predicate, $object);
        $this->assertTrue($pattern->matches($fixtureA));

        $subject = $this->getAnyPatternInstance('foo');
        $predicate = $this->getNamedNodeInstance('http://other.net');
        $object = $this->getAnyPatternInstance('foo');
        $pattern = $this->getInstance($subject, $predicate, $object);
        $this->assertFalse($pattern->matches($fixtureA));

        $subject = $this->getNamedNodeInstance('http://other.net');
        $predicate = $this->getNamedNodeInstance('http://bar.net');
        $object = $this->getLiteralInstance('baz');
        $pattern = $this->getInstance($subject, $predicate, $object);
        $this->assertFalse($pattern->matches($fixtureA));
    }

    public function testMatchesChecksIfConcrete()
    {
        $subject = $this->getAnyPatternInstance('foo');
        $predicate = $this->getAnyPatternInstance('foo');
        $object = $this->getAnyPatternInstance('foo');
        $fixture = $this->getInstance($subject, $predicate, $object);

        $subject = $this->getAnyPatternInstance('foo');
        $predicate = $this->getAnyPatternInstance('foo');
        $object = $this->getAnyPatternInstance('foo');
        $pattern = $this->getInstance($subject, $predicate, $object);

        $this->assertTrue($pattern->matches($fixture));
    }

    /*
     * Tests for toArray
     */

    public function testToArray()
    {
        $subject = $this->getNamedNodeInstance('http://s.net');
        $subject2 = $this->getBlankNodeInstance('ff');
        $predicate = $this->getNamedNodeInstance('http://p.net');
        $object = $this->getNamedNodeInstance('http://o.net');
        $graph = $this->getNamedNodeInstance('http://g.net');
        $anyGraph = $this->getAnyPatternInstance('foo');

        // s p o
        $this->assertEquals(
            [
                's' => 'http://s.net',
                'p' => 'http://p.net',
                'o' => 'http://o.net',
            ],
            $this->getInstance($subject, $predicate, $object)->toArray()
        );

        // s p o
        $this->assertEquals(
            [
                's' => '_:ff',
                'p' => 'http://p.net',
                'o' => 'http://o.net',
            ],
            $this->getInstance($subject2, $predicate, $object)->toArray()
        );

        // s p o g
        $this->assertEquals(
            [
                's' => 'http://s.net',
                'p' => 'http://p.net',
                'o' => 'http://o.net',
                'g' => 'http://g.net',
            ],
            $this->getInstance($subject, $predicate, $object, $graph)->toArray()
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Only concrete statements are supported. Yours contains at least one AnyPattern instance.
     */
    public function testToArrayAnyPattern()
    {
        $this->getInstance(
            $this->getNamedNodeInstance('foo:bar'),
            $this->getNamedNodeInstance('foo:baz'),
            $this->getNamedNodeInstance('foo:biz'),
            $this->getAnyPatternInstance('ff')
        )->toArray();
    }

    /*
     * Tests for toNQuads
     */

    public function testToNQuads()
    {
        $subject = $this->getNamedNodeInstance('http://foo.net');
        $predicate = $this->getNamedNodeInstance('http://bar.net');
        $object = $this->getNamedNodeInstance('http://baz.net');
        $graph = $this->getNamedNodeInstance('http://graph.net');
        $anyGraph = $this->getAnyPatternInstance('foo');

        $fixtureA = $this->getInstance($subject, $predicate, $object, $graph);
        $fixtureB = $this->getInstance($subject, $predicate, $object, $anyGraph);

        $expected = '<http://foo.net> <http://bar.net> <http://baz.net> <http://graph.net> .';

        $this->assertTrue($fixtureA->isQuad());
        $this->assertTrue($fixtureB->isQuad());
        $this->assertEquals($expected, $fixtureA->toNQuads());
    }

    public function testToNQuadsResource()
    {
        $node = $this->getNamedNodeInstance('http://example.org/test');
        $fixture = $this->getInstance($node, $node, $node);

        $this->assertEquals(
            '<http://example.org/test> <http://example.org/test> <http://example.org/test> .',
            $fixture->toNQuads()
        );
    }

    public function testToNQuadsResourceLiteral()
    {
        $node = $this->getNamedNodeInstance('http://example.org/test');
        $literal = $this->getLiteralInstance('http://example.org/test');
        $fixture = $this->getInstance($node, $node, $literal);

        $this->assertEquals(
            '<http://example.org/test> <http://example.org/test> '.
            '"http://example.org/test"^^<http://www.w3.org/2001/XMLSchema#string> .',
            $fixture->toNQuads()
        );
    }

    public function testToNQuadsPatternResource()
    {
        $node = $this->getNamedNodeInstance('http://example.org/test');
        $literal = $this->getLiteralInstance('http://example.org/test');
        $variable = $this->getAnyPatternInstance('foo');
        $fixture = $this->getInstance($node, $variable, $literal);

        $this->expectException('\Exception');
        $fixture->toNQuads();
    }

    /*
     * Tests for toNTriples
     */

    public function testToNTriplesNotConcreteStatement()
    {
        $fixture = $this->getInstance($this->getAnyPatternInstance('foo'), $this->getAnyPatternInstance('foo'), $this->getAnyPatternInstance('foo'));

        // Expect exception because a Statement has to be concrete in N-Triples.
        $this->expectException('\Exception');
        $fixture->toNTriples();
    }

    // test for the same result if you use a quad instead of triple
    public function testToNTriplesWithGraph()
    {
        $fixture = $this->getInstance($this->testGraph, $this->testGraph, $this->testGraph, $this->testGraph);

        $this->assertEquals(
            '<'.$this->testGraph->getUri().'> '.
            '<'.$this->testGraph->getUri().'> '.
            '<'.$this->testGraph->getUri().'> .',
            $fixture->toNTriples()
        );
    }

    public function testToNTriplesWithTriple()
    {
        $fixture = $this->getInstance($this->testGraph, $this->testGraph, $this->testGraph);

        $this->assertEquals(
            '<'.$this->testGraph->getUri().'> '.
            '<'.$this->testGraph->getUri().'> '.
            '<'.$this->testGraph->getUri().'> .',
            $fixture->toNTriples()
        );
    }

    /*
     * Tests for __toString
     */

    public function testToStringQuad()
    {
        $fixture = $this->getInstance($this->testGraph, $this->testGraph, $this->testGraph, $this->testGraph);

        $this->assertEquals(
            's: '.$this->testGraph->getUri().
            ', p: '.$this->testGraph->getUri().
            ', o: '.$this->testGraph->getUri().
            ', g: '.$this->testGraph->getUri(),
            $fixture->__toString()
        );
    }

    public function testToStringTriple()
    {
        $fixture = $this->getInstance($this->testGraph, $this->testGraph, $this->testGraph);

        $this->assertEquals(
            's: '.$this->testGraph->getUri().
            ', p: '.$this->testGraph->getUri().
            ', o: '.$this->testGraph->getUri(),
            $fixture->__toString()
        );
    }
}
