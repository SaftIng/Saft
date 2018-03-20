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

namespace Saft\Data\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\Test\TestCase;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractParserTest extends TestCase
{
    /**
     * @return Parser
     */
    abstract protected function newInstance($serialization);

    /*
     * Tests for __construct
     */

    public function testConstructorInvalidSerialization()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance('unknown');
    }

    /*
     * Tests for parseStreamToIterator
     * we load here the content of a file and transform it into an StatementIterator instance.
     * afterwards we check if the read data are the same as expected.
     */

    public function testParseStreamToIteratorTurtleFile()
    {
        $this->fixture = $this->newInstance('turtle');

        // load iterator for a turtle file
        $inputStream = dirname(__FILE__).'/../resources/example.ttl';
        $iterator = $this->fixture->parseStreamToIterator($inputStream);

        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl([
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl('RDFS label')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    'RDFS label with language tag',
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                    'en'
                )
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                new LiteralImpl("\n    Multi line comment\n    ")
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://saft/example/component'),
                new NamedNodeImpl('http://saft/example/geo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://saft/example/component'),
                new NamedNodeImpl('http://saft/example/time')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/dataset'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    'RDFS label with datatype',
                    new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string')
                )
            ),
        ]);

        $this->assertEquals($statementIteratorToCheckAgainst, $iterator);
    }

    /*
     * Tests for parseStringToIterator
     */

    public function testParseStringToIteratorTurtleString()
    {
        $xsdString = new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string');

        $fixture = $this->newInstance('turtle');

        $testString = '@prefix ex: <http://saft/example/> .
            ex:Foo  ex:knows ex:Bar ; ex:name  "Foo"^^<'.$xsdString.'> .
            ex:Bar  ex:name  "Bar"^^<'.$xsdString.'> .';

        // build StatementIterator to check against
        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl([
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foo'),
                new NamedNodeImpl('http://saft/example/knows'),
                new NamedNodeImpl('http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foo'),
                new NamedNodeImpl('http://saft/example/name'),
                new LiteralImpl('Foo', $xsdString)
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Bar'),
                new NamedNodeImpl('http://saft/example/name'),
                new LiteralImpl('Bar', $xsdString)
            ),
        ]);

        $this->assertEquals($statementIteratorToCheckAgainst, $fixture->parseStringToIterator($testString));
    }

    // that test checks if the subject can be of type blank node
    public function testParseStringToIteratorTurtleStringSubjectBlankNode()
    {
        $xsdString = new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string');

        $fixture = $this->newInstance('turtle');

        $testString = '@prefix ex: <http://saft/example/> .
            _:foo  ex:knows ex:Bar ;
                ex:name  "Foo"^^<'.$xsdString.'> .';

        $result = $fixture->parseStringToIterator($testString);

        $this->assertCountStatementIterator(2, $result);

        // check generated triples
        foreach ($result as $stmt) {
            // subject needs to be a BlankNode
            $this->assertTrue($stmt->getSubject()->isBlank());

            // predicate check
            $pUri = $stmt->getPredicate()->getUri();
            $this->assertTrue(
                'http://saft/example/knows' == $pUri || 'http://saft/example/name' == $pUri
            );

            // object check
            $this->assertTrue($stmt->getObject()->isNamed() || $stmt->getObject()->isLiteral());

            if ($stmt->getObject()->isNamed()) {
                $this->assertEquals('http://saft/example/Bar', $stmt->getObject()->getUri());
            } else { // isLiteral
                $this->assertEquals('Foo', $stmt->getObject()->getValue());
            }
        }
    }
}
