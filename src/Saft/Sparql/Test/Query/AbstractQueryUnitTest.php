<?php

namespace Saft\Sparql\Test\Query;

use Saft\Rdf\NamedNodeImpl;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\AskQuery;
use Saft\Sparql\Query\DescribeQuery;
use Saft\Sparql\Query\GraphQuery;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Query\SelectQuery;
use Saft\Sparql\Query\UpdateQuery;
use Saft\Test\TestCase;

class AbstractQueryUnitTest extends TestCase
{
    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    public function setUp()
    {
        parent::setUp();

        $this->queryFactory = new QueryFactoryImpl();

        $this->fixture = $this->getMockForAbstractClass('\Saft\Sparql\Query\AbstractQuery');
    }

    /*
     * Tests for determineObjectDatatype
     */

    public function testDetermineObjectDatatype()
    {
        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#string',
            $this->fixture->determineObjectDatatype('"Foo"')
        );

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#string',
            $this->fixture->determineObjectDatatype('"Foo"^^<http://www.w3.org/2001/XMLSchema#string>')
        );

        $this->assertEquals(
            'http://www.w3.org/2001/XMLSchema#boolean',
            $this->fixture->determineObjectDatatype('"true"^^<http://www.w3.org/2001/XMLSchema#boolean>')
        );
    }

    public function testDetermineObjectDatatypeUnknownType()
    {
        $this->assertNull($this->fixture->determineObjectDatatype('Foo'));
    }

    /*
     * Tests for determineEntityType
     */

    public function testDetermineEntityTypeLiteral()
    {
        $this->assertEquals('literal', $this->fixture->determineEntityType('"foo"@en'));
        $this->assertEquals('typed-literal', $this->fixture->determineEntityType('"foo"^^<http://foobar/>'));
        $this->assertEquals('uri', $this->fixture->determineEntityType('<'. $this->testGraph->getUri() .'>'));
        $this->assertEquals('var', $this->fixture->determineEntityType('Foo'));
    }

    public function testDetermineEntityTypeUnknownType()
    {
        $this->assertNull($this->fixture->determineEntityType(time()));
    }

    /*
     * Tests for determineObjectLanguage
     */

    public function testDetermineObjectLanguage()
    {
        $this->assertEquals(
            'en',
            $this->fixture->determineObjectLanguage('"Foo"@en')
        );
    }

    public function testDetermineObjectLanguageUnknownLanguage()
    {
        $this->assertNull($this->fixture->determineObjectLanguage('"Foo"'));
        $this->assertNull($this->fixture->determineObjectLanguage('"Foo"@'));
        $this->assertNull($this->fixture->determineObjectLanguage('"Foo@"'));
    }

    /*
     * Tests for determineObjectValue
     */

    public function testDetermineObjectValue()
    {
        $this->assertEquals(
            'Foo',
            $this->fixture->determineObjectValue('"Foo"')
        );

        // with datatype
        $this->assertEquals(
            'Foo',
            $this->fixture->determineObjectValue('"Foo"^^<http://www.w3.org/2001/XMLSchema#string>')
        );

        // with language
        $this->assertEquals(
            'Foo',
            $this->fixture->determineObjectValue('"Foo"@en')
        );
    }

    public function testDetermineObjectValueUnknownValue()
    {
        $this->assertNull($this->fixture->determineObjectValue(42));
        $this->assertNull($this->fixture->determineObjectValue('Foo'));
        $this->assertNull($this->fixture->determineObjectValue('"Foo@'));
        $this->assertNull($this->fixture->determineObjectValue('Foo@"^^<'));
    }

    /*
     * Tests extractFilterPattern
     */

    public function testExtractFilterPatternRegex()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX foo: <http://bar.de> SELECT ?s FROM <http://foo> WHERE {
                ?s <http://foobar/hey> ?o. FILTER regex(?g, "aar", "i")
             }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(
            array(
                array(
                    'args' => array(
                        array(
                            'value' => 'g',
                            'type' => 'var',
                            'operator' => ''
                        ),
                        array(
                            'value' => 'aar',
                            'type' => 'literal',
                            'sub_type' => 'literal2',
                            'operator' => ''
                        ),
                        array(
                            'value' => 'i',
                            'type' => 'literal',
                            'sub_type' => 'literal2',
                            'operator' => ''
                        ),
                    ),
                    'type' => 'built_in_call',
                    'call' => 'regex'
                ),
            ),
            $queryParts['filter_pattern']
        );
    }

    public function testExtractFilterPatternRelation()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX foo: <http://bar.de> SELECT ?s FROM <http://foo> WHERE {
                ?s <http://foobar/hey> ?o. FILTER (?o < 40)
             }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(
            array(
                array(
                    'type' => 'expression',
                    'sub_type' => 'relational',
                    'patterns' => array(
                        array(
                            'value' => 'o',
                            'type' => 'var',
                            'operator' => ''
                        ),
                        array(
                            'value' => '40',
                            'type' => 'literal',
                            'operator' => '',
                            'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                        ),
                    ),
                    'operator' => '<'
                ),
            ),
            $queryParts['filter_pattern']
        );
    }

    /*
     * Tests extractNamespacesFromQuery
     */

    public function testExtractNamespacesFromQuery()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX foo: <http://bar.de> SELECT ?s FROM <http://foo> WHERE {
                ?s <http://foobar/hey> ?o. ?s <http://foobar.de> ?o. ?s <http://www.w3.org/2001/XMLSchema#> ?o
             }'
        );

        $queryParts = $this->fixture->getQueryParts();

        // FYI: the uri http://foobar.de is not usuable for namespacing because it does not contain a / or #
        //      as last character

        $this->assertEquals(
            array(
                'ns-0' => 'http://foobar/',
                'xsd' => 'http://www.w3.org/2001/XMLSchema#'
            ),
            $queryParts['namespaces']
        );
    }

    public function testExtractNamespacesFromQueryNoNamespaces()
    {
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'SELECT ?s FROM <http://foo> WHERE { ?s ?p ?o }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['namespaces']));
    }

    /*
     * Tests extractProloguePrefixesFromQuery
     */

    public function testExtractPrefixesFromQueryNoPrefixes()
    {
        // assumption here is that fixture is of type
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'SELECT ?s FROM <http://foo> WHERE { ?s ?p ?o }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['prefixes']));
    }

    public function testExtractProloguePrefixesFromQuery()
    {
        // assumption here is that fixture is of type
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX foo: <http://bar.de> SELECT ?s FROM <http://foo> WHERE { ?s ?p ?o }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(array('foo' => 'http://bar.de'), $queryParts['prefixes']);
    }

    /*
     * Tests extractQuads
     */

    public function testExtractQuads()
    {
        // assumption here is that fixture is of type
        $this->fixture = $this->queryFactory->createInstanceByQueryString(
            'PREFIX dc: <http://foo/bar/>
            INSERT DATA {
                Graph <http://saft/test/g1> { <http://saft/test/s1> dc:p1 <http://saft/test/o1>}
                Graph <http://saft/test/g1> {<http://saft/test/s2> <http://test/p2> <http://saft/test/o2>.}
                Graph <http://saft/test/g2> {
                    <http://saft/test/s3> dc:p3 "abc"^^<http://www.w3.org/2001/XMLSchema#string>
                }
            }'
        );

        $queryParts = $this->fixture->getQueryParts();

        $this->assertEqualsArrays(
            array(
                array(
                    's' => 'http://saft/test/s1',
                    'p' => 'http://foo/bar/p1',
                    'o' => 'http://saft/test/o1',
                    's_type' => 'uri',
                    'p_type' => 'uri',
                    'o_type' => 'uri',
                    'o_datatype' => null,
                    'o_lang' => null,
                    'g' => 'http://saft/test/g1',
                    'g_type' => 'uri',
                ),
                array(
                    's' => 'http://saft/test/s2',
                    'p' => 'http://test/p2',
                    'o' => 'http://saft/test/o2',
                    's_type' => 'uri',
                    'p_type' => 'uri',
                    'o_type' => 'uri',
                    'o_datatype' => null,
                    'o_lang' => null,
                    'g' => 'http://saft/test/g1',
                    'g_type' => 'uri',
                ),
                array(
                    's' => 'http://saft/test/s3',
                    'p' => 'http://foo/bar/p3',
                    'o' => 'abc',
                    's_type' => 'uri',
                    'p_type' => 'uri',
                    'o_type' => 'typed-literal',
                    'o_datatype' => 'http://www.w3.org/2001/XMLSchema#string',
                    'o_lang' => null,
                    'g' => 'http://saft/test/g2',
                    'g_type' => 'uri',
                ),
            ),
            $queryParts['quad_pattern']
        );
    }

    /*
     * Tests extractTriplePattern
     */

    public function testExtractTriplePatternLiteral()
    {
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'o' => 'Foo',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'literal',
                    'o_datatype' => '',
                    'o_lang' => 'en'
                )
            ),
            $this->fixture->extractTriplePattern(
                '?s <http://www.w3.org/2000/01/rdf-schema#label> "Foo"@en .'
            )
        );
    }

    public function testExtractTriplePatternPrefix()
    {
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'rdfs:label',
                    'o' => 'Foo',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'literal',
                    'o_datatype' => null,
                    'o_lang' => 'en'
                )
            ),
            $this->fixture->extractTriplePattern(
                'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema>
                SELECT ?s FROM <> WHERE {
                    ?s rdfs:label "Foo"@en
                }'
            )
        );
    }

    public function testExtractTriplePatternPrefixNotFound()
    {
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'rdfs:label',
                    'o' => 'Foo',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'literal',
                    'o_datatype' => null,
                    'o_lang' => 'en'
                )
            ),
            $this->fixture->extractTriplePattern(
                'PREFIX foaf: <http://whatelse/>
                SELECT ?s FROM <> WHERE {
                    ?s rdfs:label "Foo"@en
                }'
            )
        );
    }

    public function testExtractTriplePatternTypedLiteral()
    {
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'o' => 'Foo',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'typed-literal',
                    'o_datatype' => 'http://www.w3.org/2001/XMLSchema#string',
                    'o_lang' => ''
                )
            ),
            $this->fixture->extractTriplePattern(
                '?s <http://www.w3.org/2000/01/rdf-schema#label> "Foo"^^<http://www.w3.org/2001/XMLSchema#string> .'
            )
        );
    }

    public function testExtractTriplePatternUri()
    {
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'p',
                    'o' => 'http://www.w3.org/2001/XMLSchema#string',
                    's_type' => 'var',
                    'p_type' => 'var',
                    'o_type' => 'uri',
                    'o_datatype' => null,
                    'o_lang' => null
                )
            ),
            $this->fixture->extractTriplePattern(
                '?s ?p <http://www.w3.org/2001/XMLSchema#string> .'
            )
        );
    }

    public function testExtractTriplePatternVariables()
    {
        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'p',
                    'o' => 'o',
                    's_type' => 'var',
                    'p_type' => 'var',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null
                )
            ),
            $this->fixture->extractTriplePattern('?s ?p ?o.')
        );

        $this->assertEquals(
            array(
                array(
                    's' => 's',
                    'p' => 'p',
                    'o' => 'o',
                    's_type' => 'var',
                    'p_type' => 'var',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null
                )
            ),
            $this->fixture->extractTriplePattern('?s?p?o')
        );
    }

    /**
     * Tests that all common namespaces are available
     */
    public function testGetCommmonNamespaces()
    {
        $this->assertEquals(
            array(
                'bibo'    => 'http://purl.org/ontology/bibo/',
                'cc'      => 'http://creativecommons.org/ns#',
                'cert'    => 'http://www.w3.org/ns/auth/cert#',
                'ctag'    => 'http://commontag.org/ns#',
                'dc'      => 'http://purl.org/dc/terms/',
                'dc11'    => 'http://purl.org/dc/elements/1.1/',
                'dcat'    => 'http://www.w3.org/ns/dcat#',
                'dcterms' => 'http://purl.org/dc/terms/',
                'doap'    => 'http://usefulinc.com/ns/doap#',
                'exif'    => 'http://www.w3.org/2003/12/exif/ns#',
                'foaf'    => 'http://xmlns.com/foaf/0.1/',
                'geo'     => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
                'gr'      => 'http://purl.org/goodrelations/v1#',
                'grddl'   => 'http://www.w3.org/2003/g/data-view#',
                'ical'    => 'http://www.w3.org/2002/12/cal/icaltzd#',
                'ma'      => 'http://www.w3.org/ns/ma-ont#',
                'og'      => 'http://ogp.me/ns#',
                'org'     => 'http://www.w3.org/ns/org#',
                'owl'     => 'http://www.w3.org/2002/07/owl#',
                'prov'    => 'http://www.w3.org/ns/prov#',
                'qb'      => 'http://purl.org/linked-data/cube#',
                'rdf'     => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
                'rdfa'    => 'http://www.w3.org/ns/rdfa#',
                'rdfs'    => 'http://www.w3.org/2000/01/rdf-schema#',
                'rev'     => 'http://purl.org/stuff/rev#',
                'rif'     => 'http://www.w3.org/2007/rif#',
                'rr'      => 'http://www.w3.org/ns/r2rml#',
                'rss'     => 'http://purl.org/rss/1.0/',
                'schema'  => 'http://schema.org/',
                'sd'      => 'http://www.w3.org/ns/sparql-service-description#',
                'sioc'    => 'http://rdfs.org/sioc/ns#',
                'skos'    => 'http://www.w3.org/2004/02/skos/core#',
                'skosxl'  => 'http://www.w3.org/2008/05/skos-xl#',
                'synd'    => 'http://purl.org/rss/1.0/modules/syndication/',
                'v'       => 'http://rdf.data-vocabulary.org/#',
                'vcard'   => 'http://www.w3.org/2006/vcard/ns#',
                'void'    => 'http://rdfs.org/ns/void#',
                'wdr'     => 'http://www.w3.org/2007/05/powder#',
                'wdrs'    => 'http://www.w3.org/2007/05/powder-s#',
                'wot'     => 'http://xmlns.com/wot/0.1/',
                'xhv'     => 'http://www.w3.org/1999/xhtml/vocab#',
                'xml'     => 'http://www.w3.org/XML/1998/namespace',
                'xsd'     => 'http://www.w3.org/2001/XMLSchema#',
            ),
            $this->fixture->getCommonNamespaces()
        );
    }

    /*
     * Tests for getQueryType
     */

    public function testGetQueryTypeAsk()
    {
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  ASK  { ?x foaf:name  "Alice" ;
                  foaf:mbox  <mailto:alice@work.example> }';

        $this->assertEquals('askQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeClearGraph()
    {
        $query = 'CLEAR GRAPH <';

        $this->assertEquals('graphQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeCreateGraph()
    {
        $query = 'CREATE GRAPH <';

        $this->assertEquals('graphQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeCreateSilentGraph()
    {
        $query = 'CREATE SILENT GRAPH <';

        $this->assertEquals('graphQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeDescribe()
    {
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  DESCRIBE ?x
                  WHERE { ?x foaf:mbox <mailto:alice@org> }';

        $this->assertEquals('describeQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeDropGraph()
    {
        $query = 'DROP GRAPH';

        $this->assertEquals('graphQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeDropSilentGraph()
    {
        $query = 'DROP SILENT GRAPH';

        $this->assertEquals('graphQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeInsertData()
    {
        $query = 'INSERT DATA';

        $this->assertEquals('updateQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeInsertIntoGraph()
    {
        $query = 'INSERT INTO GRAPH';

        $this->assertEquals('updateQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeSelect()
    {
        $query = ' SELECT ?x
                  FROM <'. $this->testGraph->getUri() .'>
                  WHERE { ?x foaf:mbox <mailto:alice@org> }';

        $this->assertEquals('selectQuery', AbstractQuery::getQueryType($query));
    }

    public function testGetQueryTypeUpdate()
    {
        /**
         * INSERT DATA
         */
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  INSERT DATA {Graph <>}';

        $this->assertEquals('updateQuery', AbstractQuery::getQueryType($query));

        /**
         * INSERT INTO GRAPH
         */
        $query = 'INSERT INTO GRAPH {}';

        $this->assertEquals('updateQuery', AbstractQuery::getQueryType($query));

        /**
         * DELETE
         */
        $query = 'DELETE {}';

        $this->assertEquals('updateQuery', AbstractQuery::getQueryType($query));

        /**
         * DELETE DATA
         */
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                  DELETE DATA {}';

        $this->assertEquals('updateQuery', AbstractQuery::getQueryType($query));
    }

    /*
     * Tests for setQuery
     */

    public function testSetQuery()
    {
        $this->fixture->setQuery('foo');

        $this->assertEquals('foo', $this->fixture->getQuery());
    }
}
