<?php

namespace Saft\Data\Test\Unit;

use Saft\Data\NQuadsParser;
use Saft\Data\ParserSerializerUtils;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;

class NQuadsParserTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new NQuadsParser(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new NodeUtils(new NodeFactoryImpl(), new ParserSerializerUtils()),
            new ParserSerializerUtils()
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
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/janLowC'),
                    new LiteralImpl('-2.2', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#double')),
                    new NamedNodeImpl('http://graph/2')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl('Oberbürgermeister', null, 'en'),
                    new NamedNodeImpl('http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://bnode/test'),
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl('http://graph/1')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl('http://bnode/test'),
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        'Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state '.
                        'of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the '.
                        'larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at '.
                        'the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the '.
                        'North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman '.
                        'Empire. Some \' test.\'',
                        null,
                        'en'
                    ),
                    new NamedNodeImpl('http://graph/2')
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
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/janLowC'),
                    new LiteralImpl('-2.2', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#double'))
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl('Oberbürgermeister', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://bnode/test'),
                    new BlankNodeImpl('bnode')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl('http://bnode/test'),
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
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

    public function testParseStringToIteratorQuads()
    {
        $result = $this->fixture->parseStringToIterator(
            file_get_contents(__DIR__ .'/../../resources/dbpedia-leipzig-part.nq')
        );

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/janLowC'),
                    new LiteralImpl('-2.2', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#double')),
                    new NamedNodeImpl('http://graph/2')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl('Oberbürgermeister', null, 'en'),
                    new NamedNodeImpl('http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://bnode/test'),
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl('http://graph/1')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl('http://bnode/test'),
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://graph/1')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        'Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state '.
                        'of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the '.
                        'larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at '.
                        'the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the '.
                        'North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman '.
                        'Empire. Some \' test.\'',
                        null,
                        'en'
                    ),
                    new NamedNodeImpl('http://graph/2')
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
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/janLowC'),
                    new LiteralImpl('-2.2', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#double'))
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/ontology/leaderTitle'),
                    new LiteralImpl('Oberbürgermeister', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://bnode/test'),
                    new BlankNodeImpl('bnode')
                ),
                new StatementImpl(
                    new BlankNodeImpl('bnode'),
                    new NamedNodeImpl('http://bnode/test'),
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
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
