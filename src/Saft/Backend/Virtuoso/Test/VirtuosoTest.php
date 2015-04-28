<?php
namespace Saft\Backend\Virtuoso\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\StatementImpl;
use Symfony\Component\Yaml\Parser;
use Saft\Backend\Virtuoso\Store\Virtuoso;
use Saft\Store\Test\StoreAbstractTest;

class VirtuosoTest extends StoreAbstractTest
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
    protected $testGraph;

    public function setUp()
    {
        $this->testGraph = new NamedNodeImpl('http://localhost/Saft/TestGraph/');

        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('test-config.yml missing');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));

        if (true === isset($this->config['virtuosoConfig'])) {
            $this->fixture = new Virtuoso($this->config['virtuosoConfig']);
        } elseif (true === isset($this->config['configuration']['standardStore'])
            && 'virtuoso' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new Virtuoso(
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
        }
    }

    /**
     *
     */
    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraph);
        }

        parent::tearDown();
    }

    /**
     * Tests addGraph
     */

    public function testAddGraph()
    {
        $this->assertFalse($this->fixture->isGraphAvailable($this->testGraph));

        $this->fixture->addGraph($this->testGraph);

        $this->assertTrue($this->fixture->isGraphAvailable($this->testGraph));
    }

    /**
     * function dropGraph
     */

    public function testDropGraph()
    {
        $this->fixture->dropGraph($this->testGraph);

        $this->assertFalse(
            $this->fixture->isGraphAvailable($this->testGraph)
        );

        $this->fixture->addGraph($this->testGraph);

        $this->assertTrue(
            $this->fixture->isGraphAvailable($this->testGraph)
        );

        $this->fixture->dropGraph($this->testGraph);

        $this->assertFalse(
            $this->fixture->isGraphAvailable($this->testGraph)
        );
    }

    /**
     * function getAvailableGraphs
     */

    public function testGetAvailableGraphs()
    {
        // get graph list
        $graphUris = $this->fixture->getAvailableGraphs();

        // alternative way to get the list
        $query = $this->fixture->sqlQuery(
            'SELECT ID_TO_IRI(REC_GRAPH_IID) as graph
               FROM DB.DBA.RDF_EXPLICITLY_CREATED_GRAPH'
        );

        $graphsToCheck = array();
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $graphsToCheck[$row['graph']] = $row['graph'];
        }

        $this->assertEqualsArrays($graphUris, $graphsToCheck);
    }

    /**
     * Tests getTripleCount
     */

    public function testGetTripleCount()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

        // 2 triples
        $statements = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('test literal')
            ),
        ));

        // add triples
        $this->fixture->addStatements($statements, $this->testGraph);

        // graph has to contain 2 triples
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));
    }
}
