<?php

namespace Saft\Addition\Erfurt\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Store\BasicTriplePatternStore;
use Saft\Store\Test\StoreAbstractTest;
use Symfony\Component\Yaml\Parser;

class QueryCacheTest extends StoreAbstractTest
{
    /**
     * @var BasicTriplePatternStore
     */
    protected $mockStore;

    public function setUp()
    {
        parent::setUp();

        if (true === isset($this->config['erfurtConfig'])) {
            // create mockstore to store triples in memory
            $this->mockStore = new BasicTriplePatternStore(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new StatementIteratorFactoryImpl()
            );

            // setup Erfurts QueryCache
            $this->fixture = new \Saft\Addition\Erfurt\QueryCache\QueryCache(
                new QueryFactoryImpl(),
                $this->config['erfurtConfig']
            );
            $this->fixture->setChainSuccessor($this->mockStore);

            // clean cache
            $this->fixture->getQueryCacheInstance()->cleanUpCache(array('mode' => 'clear'));

        } else {
            $this->markTestSkipped('Array erfurtConfig is not set in the test-config.yml.');
        }
    }
    
    /*
     * Tests for query method
     */

    public function testQueryDeeperCheck()
    {
        $stmtOne = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        );
        $stmtTwo = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('test literal')
        );

        $this->mockStore->addStatements(new ArrayStatementIteratorImpl(array($stmtOne, $stmtTwo)));

        // trigger QueryCache to store result
        $this->fixture->query('SELECT * FROM <http://foo> WHERE {?s ?p ?o.}');

        // empty mock store to check later on, if QueryCache uses its own data or the ones of the mock store.
        $this->mockStore = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture = new \Saft\Addition\Erfurt\QueryCache\QueryCache(
            new QueryFactoryImpl(),
            $this->config['erfurtConfig']
        );
        $this->fixture->setChainSuccessor($this->mockStore);

        // check, that it uses the cache and not the mock store
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array($stmtOne, $stmtTwo)),
            $this->fixture->query('SELECT * FROM <http://foo> WHERE {?s ?p ?o.}')
        );
    }

    // just check that a query cache returns the same list which was put in ealier
    public function testQuerySimple()
    {
        $stmtOne = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        );
        $stmtTwo = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('test literal')
        );

        $this->mockStore->addStatements(new ArrayStatementIteratorImpl(array($stmtOne, $stmtTwo)));

        $this->assertEquals(
            new ArrayStatementIteratorImpl(array($stmtOne, $stmtTwo)),
            $this->fixture->query('SELECT * FROM <http://foo> WHERE {?s ?p ?o.}')
        );
    }
}
