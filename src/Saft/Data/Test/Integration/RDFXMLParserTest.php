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
use Saft\Test\TestCase;

class RDFXMLParserTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new RDFXMLParser(new NodeFactoryImpl(), new StatementFactoryImpl(), new NodeUtils());
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
                    new NamedNodeImpl('http://dbpedia.org/resource/2015–16_MSV_Duisburg_season'),
                    new NamedNodeImpl('http://dbpedia.org/property/location'),
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl('Leipzig', null, 'de')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl('Leipzig', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl("Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman Empire.", null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl('http://umbel.org/umbel/rc/PopulatedPlace')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl('http://umbel.org/umbel/rc/Location_Underspecified')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/ontology/wikiPageID'),
                    new LiteralImpl('17955', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#integer'))
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/align'),
                    new LiteralImpl('right', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/febHighC'),
                    new LiteralImpl('4.3', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#double'))
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/febLowC'),
                    new LiteralImpl('-2', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#integer'))
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
                    new NamedNodeImpl('http://dbpedia.org/resource/2015–16_MSV_Duisburg_season'),
                    new NamedNodeImpl('http://dbpedia.org/property/location'),
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl('Leipzig', null, 'de')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl('Leipzig', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                    new LiteralImpl("Leipzig (/ˈlaɪpsɪɡ/; German: [ˈlaɪptsɪç]) is the largest city in the federal state of Saxony, Germany. It has a population of 544,479 inhabitants (1,001,220 residents in the larger urban zone). Leipzig is located about 150 kilometers (93 miles) south of Berlin at the confluence of the White Elster, Pleisse, and Parthe rivers at the southerly end of the North German Plain.Leipzig has been a trade city since at least the time of the Holy Roman Empire.", null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl('http://umbel.org/umbel/rc/PopulatedPlace')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                    new NamedNodeImpl('http://umbel.org/umbel/rc/Location_Underspecified')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/ontology/wikiPageID'),
                    new LiteralImpl('17955', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#integer'))
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/align'),
                    new LiteralImpl('right', null, 'en')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/febHighC'),
                    new LiteralImpl('4.3', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#double'))
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://dbpedia.org/resource/Leipzig'),
                    new NamedNodeImpl('http://dbpedia.org/property/febLowC'),
                    new LiteralImpl('-2', new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#integer'))
                ),
            )),
            $result
        );
    }
}
