<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\StatementImpl;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;

abstract class AbstractStatementTest extends \PHPUnit_Framework_TestCase
{
    abstract public function newLiteralInstance($value, $lang = null, $datatype = null);
    abstract public function newNamedNodeInstance($uri);
    abstract public function newAnyPatternInstance($value);
    abstract public function newBlankNodeInstance($blankId);

    /**
     * @param Node $subject
     * @param Node $predicate
     * @param Node $object
     * @param Node $graph optional
     * @return Statement
     */
    abstract public function newInstance($subject, $predicate, $object, $graph = null);

    public function testNQuadsResource()
    {
        $node = $this->newNamedNodeInstance('http://example.org/test');
        $fixture = $this->newInstance($node, $node, $node);

        $this->assertEquals(
            '<http://example.org/test> <http://example.org/test> <http://example.org/test> .',
            $fixture->toNQuads()
        );
    }

    public function testNQuadsResourceLiteral()
    {
        $node = $this->newNamedNodeInstance('http://example.org/test');
        $literal = $this->newLiteralInstance('http://example.org/test');
        $fixture = $this->newInstance($node, $node, $literal);

        $this->assertEquals(
            '<http://example.org/test> <http://example.org/test> '.
            '"http://example.org/test"^^<http://www.w3.org/2001/XMLSchema#string> .',
            $fixture->toNQuads()
        );
    }

    public function testNQuadsPatternResource()
    {
        $node = $this->newNamedNodeInstance('http://example.org/test');
        $literal = $this->newLiteralInstance('http://example.org/test');
        $variable = new AnyPatternImpl();
        $fixture = $this->newInstance($node, $variable, $literal);

        $this->setExpectedException('\Exception');
        $fixture->toNQuads();
    }

    /**
     */
    public function testMatchesChecksIfConcrete()
    {
        $subject = new AnyPatternImpl();
        $predicate = new AnyPatternImpl();
        $object = new AnyPatternImpl();
        $fixture = $this->newInstance($subject, $predicate, $object);

        $subject = new AnyPatternImpl();
        $predicate = new AnyPatternImpl();
        $object = new AnyPatternImpl();
        $pattern = new StatementImpl($subject, $predicate, $object);

        $this->assertTrue($pattern->matches($fixture));
    }

    public function testIsConcrete()
    {
        $subjectA = new AnyPatternImpl();
        $subjectB = new NamedNodeImpl("http://example.org/");
        $predicate = new NamedNodeImpl("http://example.org/");
        $object = new NamedNodeImpl("http://example.org/");
        $graphA = new AnyPatternImpl();
        $graphB = new NamedNodeImpl("http://example.org/");

        $fixtureA = $this->newInstance($subjectA, $predicate, $object);
        $fixtureB = $this->newInstance($subjectB, $predicate, $object);
        $fixtureC = $this->newInstance($subjectB, $predicate, $object, $graphA);
        $fixtureD = $this->newInstance($subjectB, $predicate, $object, $graphB);

        $this->assertFalse($fixtureA->isConcrete());
        $this->assertTrue($fixtureA->isPattern());
        $this->assertTrue($fixtureB->isConcrete());
        $this->assertFalse($fixtureB->isPattern());
        $this->assertFalse($fixtureC->isConcrete());
        $this->assertTrue($fixtureC->isPattern());
        $this->assertTrue($fixtureD->isConcrete());
        $this->assertFalse($fixtureD->isPattern());
    }

    public function testEquals()
    {
        $subjectA = new AnyPatternImpl();
        $subjectB = new NamedNodeImpl("http://example.org/");
        $predicate = new NamedNodeImpl("http://example.org/");
        $object = new NamedNodeImpl("http://example.org/");
        $graphA = new AnyPatternImpl();
        $graphB = new NamedNodeImpl("http://example.org/");

        $fixtureA = $this->newInstance($subjectA, $predicate, $object);
        $fixtureB = $this->newInstance($subjectB, $predicate, $object);
        $fixtureC = $this->newInstance($subjectB, $predicate, $object, $graphA);
        $fixtureD = $this->newInstance($subjectB, $predicate, $object, $graphB);
        $fixtureE = $this->newInstance($subjectA, $predicate, $object);
        $fixtureF = $this->newInstance($subjectB, $predicate, $object, $graphA);

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

    public function testMatches()
    {
        $subject = new NamedNodeImpl('http://foo.net');
        $predicate = new NamedNodeImpl('http://bar.net');
        $graphA = new NamedNodeImpl('http://example.net');
        $graphB = new NamedNodeImpl('http://other.net');
        $object = new LiteralImpl('baz');
        $fixtureA = $this->newInstance($subject, $predicate, $object);
        $fixtureB = $this->newInstance($subject, $predicate, $object, $graphA);
        $fixtureC = $this->newInstance($subject, $predicate, $object, $graphB);

        $any = new AnyPatternImpl();
        $patternA = new StatementImpl($any, $any, $any);
        $patternB = new StatementImpl($any, $any, $any, $any);
        $patternC = new StatementImpl($any, $any, $any, $graphA);

        // TODO check if this is realy the behavior we want
        $this->assertTrue($patternA->matches($fixtureA));
        $this->assertTrue($patternB->matches($fixtureA));
        $this->assertTrue($patternB->matches($fixtureB));
        $this->assertTrue($patternB->matches($fixtureC));
        $this->assertTrue($patternC->matches($fixtureB));
        $this->assertFalse($patternC->matches($fixtureC));

        // This assumes, that the default graph is not the union of all named graphs
        $this->assertFalse($patternA->matches($fixtureB));

        $subject = new NamedNodeImpl('http://foo.net');
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new LiteralImpl('baz');
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertTrue($pattern->matches($fixtureA));

        $subject = new AnyPatternImpl();
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new AnyPatternImpl();
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertTrue($pattern->matches($fixtureA));

        $subject = new AnyPatternImpl();
        $predicate = new NamedNodeImpl('http://other.net');
        $object = new AnyPatternImpl();
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertFalse($pattern->matches($fixtureA));

        $subject = new NamedNodeImpl('http://other.net');
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new LiteralImpl('baz');
        $pattern = new StatementImpl($subject, $predicate, $object);
        $this->assertFalse($pattern->matches($fixtureA));
    }

    public function testQuads()
    {
        $subject = new NamedNodeImpl('http://foo.net');
        $predicate = new NamedNodeImpl('http://bar.net');
        $object = new NamedNodeImpl('http://baz.net');
        $graph = new NamedNodeImpl('http://graph.net');
        $anyGraph = new AnyPatternImpl();

        $fixtureA = $this->newInstance($subject, $predicate, $object, $graph);
        $fixtureB = $this->newInstance($subject, $predicate, $object, $anyGraph);

        $expected = "<http://foo.net> <http://bar.net> <http://baz.net> <http://graph.net> .";

        $this->assertTrue($fixtureA->isQuad());
        $this->assertTrue($fixtureB->isQuad());
        $this->assertEquals($expected, $fixtureA->toNQuads());
    }
}
