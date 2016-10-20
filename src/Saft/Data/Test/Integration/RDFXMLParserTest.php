<?php

namespace Saft\Data\Test\Unit;

use Saft\Data\RDFXMLParser;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Test\TestCase;

class RDFXMLParserTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new RDFXMLParser(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils()
        );
    }

    /*
     * Tests for parseStreamToIterator
     */

    public function testParseStreamToIterator()
    {
        $result = $this->fixture->parseStreamToIterator(__DIR__ .'/../../resources/dbpedia-leipzig-part.rdf');

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/2015–16_MSV_Duisburg_season'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/location'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl(new NodeUtils(), 'Leipzig', null, 'de')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl(new NodeUtils(), 'Leipzig', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        new NodeUtils(),
                        "Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state of Saxony, "
                        ."Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the larger "
                        ."urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at the "
                        ."confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the North"
                        ." German Plain.Leipzig has been a trade city since at least the time of the Holy Roman "
                        ."Empire.",
                        null,
                        'en'
                    )
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl(new NodeUtils(), 'http://umbel.org/umbel/rc/PopulatedPlace')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl(new NodeUtils(), 'http://umbel.org/umbel/rc/Location_Underspecified')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/ontology/wikiPageID'),
                    new LiteralImpl(new NodeUtils(), '17955', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#integer'))
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/align'),
                    new LiteralImpl(new NodeUtils(), 'right', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/febHighC'),
                    new LiteralImpl(
                        new NodeUtils(),
                        '4.3',
                        new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#double')
                    )
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/febLowC'),
                    new LiteralImpl(
                        new NodeUtils(),
                        '-2',
                        new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#integer')
                    )
                ),
            )),
            $result
        );
    }

    /*
     * Tests for parseStringToIterator
     */

    public function testParseStringToIterator()
    {
        $data = file_get_contents(__DIR__ .'/../../resources/dbpedia-leipzig-part.rdf');
        $result = $this->fixture->parseStringToIterator($data);

        $this->assertStatementIteratorEquals(
            new ArrayStatementIteratorImpl(array(
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/2015–16_MSV_Duisburg_season'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/location'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl(new NodeUtils(), 'Leipzig', null, 'de')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl(new NodeUtils(), 'Leipzig', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl(
                        new NodeUtils(),
                        "Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state of Saxony, "
                        ."Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the larger "
                        ."urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at the "
                        ."confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the North"
                        ." German Plain.Leipzig has been a trade city since at least the time of the Holy Roman "
                        ."Empire.",
                        null,
                        'en'
                    )
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl(new NodeUtils(), 'http://umbel.org/umbel/rc/PopulatedPlace')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl(new NodeUtils(), 'http://umbel.org/umbel/rc/Location_Underspecified')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/ontology/wikiPageID'),
                    new LiteralImpl(new NodeUtils(), '17955', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#integer'))
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/align'),
                    new LiteralImpl(new NodeUtils(), 'right', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/febHighC'),
                    new LiteralImpl(new NodeUtils(), '4.3', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#double'))
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl(new NodeUtils(), 'http://dbpedia.org/property/febLowC'),
                    new LiteralImpl(new NodeUtils(), '-2', new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#integer'))
                ),
            )),
            $result
        );
    }
}
