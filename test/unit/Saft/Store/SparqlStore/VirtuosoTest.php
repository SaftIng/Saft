<?php
namespace Saft\Store\SparqlStore;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Literal;
use Saft\Rdf\StatementImpl;
use Saft\Store\TestCase;

class VirtuosoUnitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (true === isset($this->config['virtuosoConfig'])) {
            $this->fixture = new \Saft\Store\SparqlStore\Virtuoso($this->config['virtuosoConfig']);
        } elseif ('virtuoso' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new \Saft\Store\SparqlStore\Virtuoso(
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped(
                'Array virtuosoConfig is not set in the config.yml.'
            );
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
     * function dropGraph
     */

    public function testDropGraph()
    {
        $this->fixture->dropGraph($this->testGraphUri);

        $this->assertFalse(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );

        $this->fixture->addGraph($this->testGraphUri);

        $this->assertTrue(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );

        $this->fixture->dropGraph($this->testGraphUri);

        $this->assertFalse(
            $this->fixture->isGraphAvailable($this->testGraphUri)
        );
    }

    /**
     * Tests existence of Virtuoso class
     */
    public function testExistence()
    {
        $this->assertTrue(class_exists('\Saft\Store\SparqlStore\Virtuoso'));
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

        // graph has to contain 2 triples
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraphUri));
    }

    /**
     * Tests getServiceDescription
     */

    public function testGetStoreDescription()
    {
        $this->assertEquals(array(), $this->fixture->getStoreDescription());
    }
}
