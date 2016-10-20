<?php

namespace Saft\Data\Test\Unit;

use Saft\Data\NQuadsParser;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;

class NQuadsParserTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new NQuadsParser(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils()
        );
    }

    /*
     * Tests for parseStreamToIterator
     */

    public function testParseStreamToIteratorQuads()
    {
        $result = $this->fixture->parseStreamToIterator(__DIR__ .'/../../resources/dbpedia-leipzig-part.nq');

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/janLowC'),
                    new LiteralImpl(new NodeUtils(), '-2.2', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#double')),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/2')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl(new NodeUtils(), 'Oberbürgermeister', null, 'en'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/1')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        new NodeUtils(),
                        'Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state '.
                        'of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the '.
                        'larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at '.
                        'the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the '.
                        'North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman '.
                        'Empire. Some \' test.\'',
                        null,
                        'en'
                    ),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/2')
                ),
            )),
            $result
        );
    }

    public function testParseStreamToIteratorTriples()
    {
        $result = $this->fixture->parseStreamToIterator(__DIR__ .'/../../resources/dbpedia-leipzig-part.nt');

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/janLowC'),
                    new LiteralImpl(new NodeUtils(), '-2.2', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#double'))
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl(new NodeUtils(), 'Oberbürgermeister', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new BlankNodeImpl('bnode')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        new NodeUtils(),
                        'Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state '.
                        'of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the '.
                        'larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at '.
                        'the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the '.
                        'North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman '.
                        'Empire. Some \' test.\'',
                        null,
                        'en'
                    )
                ),
            )),
            $result
        );
    }

    /*
     * Tests for parseStringToIterator
     */

    public function testParseStringToIteratorCheckBlankNodeUriBlankNode()
    {
        $result = $this->fixture->parseStringToIterator(
            '_:bnode1 <http://locahost/#foo1> _:bnode2 .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new BlankNodeImpl('bnode1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new BlankNodeImpl('bnode2')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckBlankNodeUriUri()
    {
        $result = $this->fixture->parseStringToIterator(
            '_:bnode1 <http://locahost/#foo1> <http://locahost/#foo2> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new BlankNodeImpl('bnode1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo2')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckUriUriBlankNode()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> _:bnode .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new BlankNodeImpl('bnode')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckUriUriLiteral()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> "Foo" .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new LiteralImpl(new NodeUtils(), 'Foo')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckUriUriLanguagedLiteral()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> "Foo"@de .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new LiteralImpl(new NodeUtils(), 'Foo', null, 'de')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckUriUriTypedLiteral()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> "Foo"^^<http://www.w3.org/2001/XMLSchema#string> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new LiteralImpl(new NodeUtils(), 'Foo', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#string'))
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckUriUriUri()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> <http://locahost/#foo2> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo2')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckQuadBlankNodeUriBlankNode()
    {
        $result = $this->fixture->parseStringToIterator(
            '_:bnode1 <http://locahost/#foo1> _:bnode2 <http://graph/> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new BlankNodeImpl('bnode1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new BlankNodeImpl('bnode2'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckQuadBlankNodeUriUri()
    {
        $result = $this->fixture->parseStringToIterator(
            '_:bnode1 <http://locahost/#foo1> <http://locahost/#foo2> <http://graph/> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new BlankNodeImpl('bnode1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo2'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckQuadUriUriBlankNode()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> _:bnode <http://graph/> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckQuadUriUriLiteral()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> "Foo" .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new LiteralImpl(new NodeUtils(), 'Foo')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckQuadUriUriLanguagedLiteral()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> "Foo"@de .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new LiteralImpl(new NodeUtils(), 'Foo', null, 'de')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckQuadUriUriTypedLiteral()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> "Foo"^^<http://www.w3.org/2001/XMLSchema#string> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new LiteralImpl(new NodeUtils(), 'Foo', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#string'))
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorCheckQuadUriUriUri()
    {
        $result = $this->fixture->parseStringToIterator(
            '<http://locahost/#foo> <http://locahost/#foo1> <http://locahost/#foo2> <http://graph/> .'
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://locahost/#foo2'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/')
                )
            )),
            $result
        );
    }

    public function testParseStringToIteratorQuads()
    {
        $result = $this->fixture->parseStringToIterator(
            file_get_contents(__DIR__ .'/../../resources/dbpedia-leipzig-part.nq')
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/janLowC'),
                    new LiteralImpl(new NodeUtils(), '-2.2', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#double')),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/2')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl(new NodeUtils(), 'Oberbürgermeister', null, 'en'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/1')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        new NodeUtils(),
                        'Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state '.
                        'of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the '.
                        'larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at '.
                        'the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the '.
                        'North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman '.
                        'Empire. Some \' test.\'',
                        null,
                        'en'
                    ),
                    new NamedNodeImpl(new NodeUtils(), 'http://graph/2')
                ),
            )),
            $result
        );
    }

    public function testParseStringToIteratorTriples()
    {
        $result = $this->fixture->parseStringToIterator(file_get_contents(__DIR__ .'/../../resources/dbpedia-leipzig-part.nt'));

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/janLowC'),
                    new LiteralImpl(new NodeUtils(), '-2.2', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#double'))
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl(new NodeUtils(), 'Oberbürgermeister', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new BlankNodeImpl('bnode')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl(new NodeUtils(), 'http://bnode/test'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        new NodeUtils(),
                        'Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state '.
                        'of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the '.
                        'larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at '.
                        'the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the '.
                        'North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman '.
                        'Empire. Some \' test.\'',
                        null,
                        'en'
                    )
                ),
            )),
            $result
        );
    }
}
