<?php
namespace Saft\Store\SparqlStore;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Literal;
use Saft\Rdf\StatementImpl;
use Symfony\Component\Yaml\Parser;

class HttpIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Saft\Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $config;
    
    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    /**
     * @var string
     */
    protected $testGraphUri = 'http://localhost/Saft/TestGraph/';
    
    public function setUp()
    {
        // set path to root dir, usually where to saft-skeleton
        // TODO move config.yml stuff to Saft.store package
        $saftRootDir = dirname(__FILE__) . '/../../../../';
        $configFilepath = $saftRootDir . 'config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('config.yml missing in test/config.yml');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));

        if (true === isset($this->config['httpConfig'])) {
            $this->fixture = new \Saft\Store\SparqlStore\Http($this->config['httpConfig']);
        } elseif ('http' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new \Saft\Store\SparqlStore\Http(
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped('Array httpConfig is not set in the config.yml.');
        }
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->fixture->dropGraph($this->testGraphUri);

        parent::tearDown();
    }
    
    /**
     * http://stackoverflow.com/a/12496979
     * Fixes assertEquals in case of check array equality.
     *
     * @param array  $expected
     * @param array  $actual
     * @param string $message  optional
     */
    protected function assertEqualsArrays($expected, $actual, $message = "")
    {
        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * function addStatements
     */

    public function testAddStatementsLanguageTags()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new Literal('test literal', 'en')
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new Literal('test literal', 'de')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        // graph has now two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));
    }

    public function testAddStatements()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new NamedNode('http://o/')
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new Literal('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        // graph has two entries
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));
    }

    /**
     * function deleteMatchingStatements
     */

    public function testDeleteMatchingStatements()
    {
        /**
         * Create some test data
         */
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new NamedNode('http://o/')
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new Literal('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));

        /**
         * drop all triples
         */
        $this->fixture->deleteMatchingStatements(
            new StatementImpl(new NamedNode('http://s/'), new NamedNode('http://p/'), new NamedNode(null)),
            $this->testGraphUri
        );

        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));
    }


    /**
     * function query
     */

    public function testQuery()
    {
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraphUri));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new NamedNode('http://o/')
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new NamedNode('http://o/1')
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/'),
                new NamedNode('http://o/2')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        $this->assertEquals(
            array(array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/1"
            ), array(
                "s" => "http://s/", "p" => "http://p/", "o" => "http://o/2"
            )),
            $this->fixture->query(
                'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}'
            )
        );
    }

    public function testQueryDifferentResultTypes()
    {
        $this->assertEquals(
            array(),
            $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}')
        );

        $this->assertEquals(
            array(),
            $this->fixture->query(
                'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}',
                array('resultType' => 'array')
            )
        );
    }

    public function testQueryEmptyResult()
    {
        $this->assertEquals(
            array(),
            $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}')
        );
    }

    public function testQueryExtendedResult()
    {
        // triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/1'),
                new Literal('val EN', 'en')
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/2'),
                new Literal('val DE', 'de')
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/3'),
                new Literal(1337)
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/4'),
                new Literal(0)
            ),
            new StatementImpl(
                new NamedNode('http://s/'),
                new NamedNode('http://p/5'),
                new Literal(false)
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraphUri);

        $this->assertEqualsArrays(
            array(
                'head' => array(
                    'link' => array(),
                    'vars' => array('s', 'p', 'o')
                ),
                'results' => array(
                    'distinct'  => false,
                    'ordered'   => true,
                    'bindings'  => array(
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/1'
                            ),
                            'o' => array(
                                'type'      => 'literal',
                                'value'     => 'val EN',
                                'xml:lang'  => 'en'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/2'
                            ),
                            'o' => array(
                                'xml:lang'  => 'de',
                                'type'      => 'literal',
                                'value'     => 'val DE'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/3'
                            ),
                            'o' => array(
                                'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer',
                                'type'      => 'typed-literal',
                                'value'     => '1337'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/4'
                            ),
                            'o' => array(
                                'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer',
                                'type'      => 'typed-literal',
                                'value'     => '0'
                            )
                        ),
                        array(
                            's' => array(
                                'type'  => 'uri',
                                'value' => 'http://s/'
                            ),
                            'p' => array(
                                'type'  => 'uri',
                                'value' => 'http://p/5'
                            ),
                            // The value was false, but now its 0. Virtuoso cast boolean values to 0 or 1.
                            // The problem is, after you fetch entries, the datatype changed, like here, from
                            // xsd:boolean to xsd:integer.
                            // TODO how to handle boolean values? save boolean, but fetch integer later on...
                            'o' => array(
                                'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer',
                                'type'      => 'typed-literal',
                                'value'     => '0'
                            )
                        )
                    )
                )
            ),
            $this->fixture->query(
                'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.} ORDER BY ?p',
                array('resultType' => 'extended')
            )
        );
    }

    public function testQueryInvalidResultType()
    {
        $this->setExpectedException('\Exception');

        $this->fixture->query(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}',
            array('resultType' => 'invalid')
        );
    }
}
