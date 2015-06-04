<?php

namespace Saft\Addition\QueryCache\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Test\BasicTriplePatternStore;
use Saft\Test\TestCase;
use Symfony\Component\Yaml\Parser;

/**
 * That abstract class provides tests for the QueryCache component. But it will not be executed directly but
 * over subclasses with cache backend as suffix, such as QueryCacheFileCacheTest.php.
 *
 * This way we can run all the tests for different configuration with minimum overhead.
 */
abstract class AbstractQueryCacheIntegrationTest extends TestCase
{
    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * Used in pattern key's as seperator. Here an example for _:
     * http://localhost/Saft/TestGraph/_http://a_*_*
     *
     * @var string
     */
    protected $separator = '__.__';

    public function setUp()
    {
        parent::setUp();

        $this->queryFactory = new QueryFactoryImpl();
    }

    /*
     * Tests for addStatements
     */

    public function testAddStatements()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // build testdata
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));

        // assumption is that all given parameter will be returned
        $this->assertNull($this->fixture->addStatements($statementIterator, $this->testGraph, array(1)));
    }

    // try to call function method without a successor set leads to an exception
    public function testAddStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');

        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));

        $this->fixture->addStatements($statementIterator);
    }

    /*
     * Tests for buildPatternListBySPO
     */

    public function testBuildPatternListBySPO3Placeholders()
    {
        $this->assertEquals(
            array(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            ),
            $this->fixture->buildPatternListBySPO(
                '*', // s
                '*', // p
                '*', // o
                $this->testGraph->getUri()
            )
        );
    }

    public function testBuildPatternListBySPOWithUriAndPlaceholders()
    {
        $sUri = 'http://foo/subject';

        $this->assertEquals(
            array(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
                $this->testGraph->getUri() . $this->separator . $sUri . $this->separator .'*'. $this->separator .'*'
            ),
            $this->fixture->buildPatternListBySPO(
                $sUri, // s
                '*', // p
                '*', // o
                $this->testGraph->getUri()
            )
        );
    }

    /*
     * Tests for buildPatternListByStatement
     */

    public function testBuildPatternListByStatement()
    {
        $statement = new StatementImpl(
            new NamedNodeImpl('http://a'),
            new NamedNodeImpl('http://b'),
            new NamedNodeImpl('http://c')
        );

        $this->assertEquals(
            array(

                // graphUri_*_*_*
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'*'. $this->separator .'*',

                /**
                 * 1 place set: graphUri_http://a_*_*
                 */
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .
                    '*'. $this->separator .'*',
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'http://b'.
                    $this->separator .'*',
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'*'.
                    $this->separator .'http://c',

                /**
                 * 2 places set: graphUri_http://a_http://b_*
                 */
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .'http://b'.
                    $this->separator .'*',
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .'*'.
                    $this->separator .'http://c',
                'http://localhost/Saft/TestGraph/'. $this->separator .'*'. $this->separator .'http://b'.
                    $this->separator .'http://c',

                /**
                 * all 3 places set
                 */
                'http://localhost/Saft/TestGraph/'. $this->separator .'http://a'. $this->separator .'http://b'.
                    $this->separator .'http://c',
            ),
            $this->fixture->buildPatternListByStatement($statement, $this->testGraph->getUri())
        );
    }

    public function testBuildPatternListByStatementOnlyVariables()
    {
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->assertEquals(
            array(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',
            ),
            $this->fixture->buildPatternListByStatement($statement, $this->testGraph)
        );
    }

    /*
     * Tests for buildPatternListByTriplePattern
     */

    public function testBuildPatternListByTriplePattern()
    {
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->assertEquals(
            array(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*',

                /**
                 * 1 place set
                 */
                $this->testGraph->getUri() . $this->separator .'http://a'. $this->separator .'*'. $this->separator .'*',
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'http://b'. $this->separator .'*',
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'http://c',

                /**
                 * 2 places set
                 */
                $this->testGraph->getUri() . $this->separator .'http://a'. $this->separator .'http://b'.
                    $this->separator .'*',
                $this->testGraph->getUri() . $this->separator .'http://a'. $this->separator .'*'.
                    $this->separator .'http://c',
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'http://b'.
                    $this->separator .'http://c',

                // all 3 places set
                $this->testGraph->getUri() . $this->separator .'http://a'. $this->separator .'http://b'.
                    $this->separator .'http://c',
            ),
            $this->fixture->buildPatternListByTriplePattern(
                array(
                    's' => 'http://a',
                    'p' => 'http://b',
                    'o' => 'http://c',
                    's_type' => 'uri',
                    'p_type' => 'uri',
                    'o_type' => 'uri',
                ),
                $this->testGraph->getUri()
            )
        );
    }

    public function testBuildPatternListByTriplePatternOnlyPlaceholders()
    {
        $this->assertEquals(
            array(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            ),
            $this->fixture->buildPatternListByTriplePattern(
                array(
                    's' => '*',
                    'p' => '*',
                    'o' => '*',
                    's_type' => '',
                    'p_type' => '',
                    'o_type' => '',
                ),
                $this->testGraph->getUri()
            )
        );
    }

    /*
     * Tests for deleteMatchingStatements
     */

    public function testDeleteMatchingStatements()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // build testdata
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        // assumption is that all given parameter will be returned
        $this->assertNull($this->fixture->deleteMatchingStatements($statement, $this->testGraph, array(1)));
    }

    // try to call function method without a successor set leads to an exception
    public function testDeleteMatchingStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');

        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->fixture->deleteMatchingStatements($statement);
    }

    /*
     * Tests for get- and setChainSuccessor
     */

    public function testGetAndSetChainSuccessor()
    {
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );

        $this->fixture->setChainSuccessor($successor);

        $this->assertEquals($successor, $this->fixture->getChainSuccessor());
    }

    /*
     * Tests getGraphs
     */

    public function testGetGraphs()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array(),
            $this->fixture->getGraphs()
        );
    }

    // try to call function method without a successor set leads to an exception
    public function testGetGraphsNoSuccessor()
    {
        $this->setExpectedException('\Exception');

        $this->fixture->getGraphs();
    }


    /*
     * Tests for getLog
     *
     * The following functions tests the log result for certain function calls
     */

    // TODO implement this test using @depends
    public function testGetLogAddStatements()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // build testdata
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));

        $options = array(1);

        $this->fixture->addStatements($statementIterator, $this->testGraph, $options);

        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'addStatements',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraph->getUri(),
                        'options' => $options
                    )
                ),
                array(
                    'method' => 'invalidateByTriplePattern',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'buildPatternListByStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO',
                    'parameter' => array(
                        's' => '*',
                        'p' => '*',
                        'o' => '*',
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogBuildPatternListByStatement()
    {
        $statement = new StatementImpl(
            new NamedNodeImpl('http://a'),
            new NamedNodeImpl('http://b'),
            new NamedNodeImpl('http://c')
        );

        $this->fixture->buildPatternListByStatement($statement, $this->testGraph->getUri());

        $this->assertEquals(
            array(
                array(
                    'method' => 'buildPatternListByStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO',
                    'parameter' => array(
                        's' => 'http://a',
                        'p' => 'http://b',
                        'o' => 'http://c',
                        'graphUri' => $this->testGraph->getUri()
                    )
                )
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogDeleteMatchingStatements()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // build testdata
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);

        $this->fixture->deleteMatchingStatements($statement, $this->testGraph, $options);

        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'deleteMatchingStatements',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraph->getUri(),
                        'options' => $options
                    )
                ),
                array(
                    'method' => 'invalidateByTriplePattern',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'buildPatternListByStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO',
                    'parameter' => array(
                        's' => '*',
                        'p' => '*',
                        'o' => '*',
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogGetMatchingStatements()
    {
        $this->markTestSkipped("We need variables for this");
        // set basic store as successor
        $successor = new BasicTriplePatternStore(new NodeFactoryImpl());
        $this->fixture->setChainSuccessor($successor);

        // build testdata
        $statement = new StatementImpl(new VariableImpl('?s'), new VariableImpl('?p'), new VariableImpl('?o'));
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);

        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }';
        $queryObject = $this->queryFactory->createInstanceByQueryString($query);
        $queryObject->getQueryParts();

        $this->fixture->getMatchingStatements($statement, $this->testGraph, $options);

        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'getMatchingStatements',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraph->getUri(),
                        'options' => $options
                    )
                ),
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => array(
                            $statement,
                            $this->testGraph->getUri(),
                            array(1)
                        )
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                )
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogGetStoreDescription()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        $this->fixture->getStoreDescription();

        $this->assertEquals(
            array(
                // that was called by us directly
                array('method' => 'getStoreDescription')
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogHasMatchingStatement()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // build testdata
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);

        // save result of the function call
        $result = $this->fixture->hasMatchingStatement($statement, $this->testGraph, $options);

        // query
        $queryObject = $this->queryFactory->createInstanceByQueryString(
            'ASK FROM <'. $this->testGraph->getUri() .'> { ?s ?p ?o }'
        );
        $queryObject->getQueryParts();

        $this->assertEquals(
            array(
                // that was called by us directly
                array(
                    'method' => 'hasMatchingStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraph->getUri(),
                        'options' => $options,
                    )
                ),
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => $result
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogInvalidateByGraphUri()
    {
        /**
         * First create test data and save it via saveResult
         */
        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }';
        $queryObject = $this->queryFactory->createInstanceByQueryString($query);

        $result = array(1, 2, 3);

        $this->fixture->saveResult($queryObject, $result);

        /**
         * Invalidate everything via a invalidateByQuery call
         */
        $this->fixture->invalidateByGraphUri($this->testGraph->getUri());

        // check log for method calls done
        $this->assertEquals(
            array(
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => $result
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
                array(
                    'method' => 'invalidateByGraphUri',
                    'parameter' => array(
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $this->queryFactory->createInstanceByQueryString($query)
                    )
                )
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogInvalidateByQuery()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = $this->queryFactory->createInstanceByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }'
        );

        $result = array(1, 2, 3);

        $this->fixture->saveResult($queryObject, $result);

        /**
         * Invalidate everything via a invalidateByQuery call
         */
        $this->fixture->invalidateByQuery($queryObject);

        // check log for method calls done
        $this->assertEquals(
            array(
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => $result
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    // tests invalidateByTriplePattern with statement consisting of 3 variables and graph URI given
    public function testGetLogInvalidateByTriplePatternGraph3VariablesAndUriGiven()
    {
        /**
         * First create test data and save it via saveResult
         */
        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }';
        $queryObject = $this->queryFactory->createInstanceByQueryString($query);

        $result = array(1, 2, 3);

        $this->fixture->saveResult($queryObject, $result);

        /**
         * Invalidate everything via a invalidateByTriplePattern call
         */
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));

        $this->fixture->invalidateByTriplePattern($statementIterator, $this->testGraph->getUri());

        // check log for method calls done
        $this->assertEquals(
            array(
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => $result
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
                array(
                    'method' => 'invalidateByTriplePattern',
                    'parameter' => array(
                        'statements' => $statementIterator,
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'buildPatternListByStatement',
                    'parameter' => array(
                        'statement' => $statement,
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'buildPatternListBySPO',
                    'parameter' => array(
                        's' => '*',
                        'p' => '*',
                        'o' => '*',
                        'graphUri' => $this->testGraph->getUri()
                    )
                ),
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $this->queryFactory->createInstanceByQueryString($query)
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }

    // TODO implement this test using @depends
    public function testGetLogQuery()
    {
        $this->markTestSkipped("We need variables for this");
        // set basic store as successor
        $successor = new BasicTriplePatternStore(new NodeFactoryImpl());
        $this->fixture->setChainSuccessor($successor);

        // build testdata
        $query = 'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }';
        $queryObject = $this->queryFactory->createInstanceByQueryString($query);
        $queryObject->getQueryParts();

        $options = array();

        $this->fixture->query($query, $options);

        // check log for method calls done
        $this->assertEquals(
            array(
                // was directly called by us
                array(
                    'method' => 'query',
                    'parameter' => array(
                        'query' => $query,
                        'options' => $options
                    )
                ),
                // saves query result
                array(
                    'method' => 'saveResult',
                    'parameter' => array(
                        'queryObject' => $queryObject,
                        'result' => array(
                            new StatementImpl(
                                new AnyPatternImpl('?s'),
                                new AnyPatternImpl('?p'),
                                new AnyPatternImpl('?o')
                            ),
                            null,
                            array()
                        )
                    )
                ),
                // invalidate previous saved result, if available
                array(
                    'method' => 'invalidateByQuery',
                    'parameter' => array(
                        'queryObject' => $queryObject
                    )
                ),
            ),
            $this->fixture->getLog()
        );
    }

    /*
     * Tests for getMatchingStatement
     */

    public function testGetMatchingStatementsNamedNodes()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        );
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);

        // assumption is that all given parameter will be returned
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array()),
            $this->fixture->getMatchingStatements($statement, $this->testGraph, $options)
        );

        $this->assertEquals(
            array(
                array(
                    'graph_uris' => array($this->testGraph->getUri() => $this->testGraph->getUri()),
                    'query' => 'SELECT ?s ?p ?o FROM <http://localhost/Saft/TestGraph/> '.
                               'WHERE { '.
                                    '?s ?p ?o '.
                                    'FILTER (str(?s) = "'. $statement->getSubject()->getUri() .'") '.
                                    'FILTER (str(?p) = "'. $statement->getPredicate()->getUri() .'") '.
                                    'FILTER (str(?o) = "'. $statement->getObject()->getUri() .'") '.
                                '}',
                    'result' => new ArrayStatementIteratorImpl(array()),
                    'triple_pattern' => array(
                        $this->testGraph->getUri() .
                        $this->separator .'*'. $this->separator .'*'. $this->separator .'*' =>
                            $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'.
                            $this->separator .'*'
                    )
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }

    public function testGetMatchingStatementsNamedNodesLiteral()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('test literal')
        );
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);

        // assumption is that all given parameter will be returned
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array()),
            $this->fixture->getMatchingStatements($statement, $this->testGraph, $options)
        );

        $this->assertEquals(
            array(
                array(
                    'graph_uris' => array($this->testGraph->getUri() => $this->testGraph->getUri()),
                    'query' => 'SELECT ?s ?p ?o FROM <http://localhost/Saft/TestGraph/> '.
                               'WHERE { '.
                                    '?s ?p ?o '.
                                    'FILTER (str(?s) = "'. $statement->getSubject()->getUri() .'") '.
                                    'FILTER (str(?p) = "'. $statement->getPredicate()->getUri() .'") '.
                                    'FILTER (str(?o) = "'. $statement->getObject()->getValue() .'") '.
                                '}',
                    'result' => new ArrayStatementIteratorImpl(array()),
                    'triple_pattern' => array(
                        $this->testGraph->getUri() .
                        $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
                        => $this->testGraph->getUri() .
                        $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
                    )
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }

    public function testGetMatchingStatementsNoSuccessor()
    {
        $this->setExpectedException('\Exception');

        // test data
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new LiteralImpl('test literal')
        );
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);

        $this->fixture->getMatchingStatements($statement, $this->testGraph, $options);
    }

    public function testGetMatchingStatementsUseCachedResult()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s/'),
            new NamedNodeImpl('http://p/'),
            new NamedNodeImpl('http://o/')
        );
        $statementIterator = new ArrayStatementIteratorImpl(array($statement));
        $options = array(1);

        // assumption is that all given parameter will be returned
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array()),
            $this->fixture->getMatchingStatements($statement, $this->testGraph, $options)
        );

        // call getMatchingStatements again and see what happens
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array()),
            $this->fixture->getMatchingStatements($statement, $this->testGraph, $options)
        );

        // check latest query cache container
        $this->assertEquals(
            array(
                array(
                    'graph_uris' => array($this->testGraph->getUri() => $this->testGraph->getUri()),
                    'query' => 'SELECT ?s ?p ?o FROM <http://localhost/Saft/TestGraph/> '.
                               'WHERE { '.
                                    '?s ?p ?o '.
                                    'FILTER (str(?s) = "'. $statement->getSubject()->getUri() .'") '.
                                    'FILTER (str(?p) = "'. $statement->getPredicate()->getUri() .'") '.
                                    'FILTER (str(?o) = "'. $statement->getObject()->getUri() .'") '.
                                '}',
                    'result' => new ArrayStatementIteratorImpl(array()),
                    'triple_pattern' => array(
                        $this->testGraph->getUri() .
                        $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
                        => $this->testGraph->getUri() .
                        $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
                    )
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }

    public function testGetMatchingStatementsVariables()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());
        $options = array(1);

        // assumption is that all given parameter will be returned
        $this->assertEquals(
            new ArrayStatementIteratorImpl(array()),
            $this->fixture->getMatchingStatements($statement, $this->testGraph, $options)
        );
    }

    /*
     * Tests for getResult
     */

    public function testGetResultQueryObject()
    {
        $queryObject = $this->queryFactory->createInstanceByQueryString(
            'SELECT * FROM <http://graph/> WHERE {?s ?p ?o}'
        );

        $this->assertNull($this->fixture->getResult($queryObject));
    }

    public function testGetResultString()
    {
        $this->assertNull($this->fixture->getResult('foo'));
    }

    public function testGetResultInvalidParameter()
    {
        $this->setExpectedException('\Exception');

        $this->fixture->getResult(032);
    }

    /*
     * Tests for getStoreDescription
     */

    public function testGetStoreDescription()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // assumption is that all given parameter will be returned
        $this->assertEquals(
            array(),
            $this->fixture->getStoreDescription()
        );
    }

    // try to call function method without a successor set leads to an exception
    public function testGetStoreDescriptionNoSuccessor()
    {
        $this->setExpectedException('\Exception');

        $this->fixture->getStoreDescription();
    }

    /*
     * Tests for hasMatchingStatement
     */

    public function testHasMatchingStatementNamedNodesLiteral()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s'),
            new NamedNodeImpl('http://p'),
            new LiteralImpl('foo')
        );

        $this->fixture->addStatements(array($statement), $this->testGraph);

        $options = array(1);

        // assumption is that all given parameter will be returned
        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph, $options));
    }

    public function testHasMatchingStatementOnlyNamedNodes()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(
            new NamedNodeImpl('http://s'),
            new NamedNodeImpl('http://p'),
            new NamedNodeImpl('http://o')
        );

        $this->fixture->addStatements(array($statement), $this->testGraph);

        $options = array(1);

        // assumption is that all given parameter will be returned
        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph, $options));
    }

    // try to call function method without a successor set leads to an exception
    public function testHasMatchingStatementNoSuccessor()
    {
        $this->setExpectedException('\Exception');

        // test data
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->fixture->hasMatchingStatement($statement);
    }

    public function testHasMatchingStatementOnlyVariables()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->fixture->addStatements(array($statement), $this->testGraph);

        $options = array(1);

        // assumption is that the mock just returns the previously added $statement, even if
        // it only contains non-concrete nodes.
        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph, $options));
    }

    public function testHasMatchingStatementUseCachedResult()
    {
        // set basic store as successor
        $successor = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
        $this->fixture->setChainSuccessor($successor);

        // test data
        $statement = new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl());

        $this->fixture->addStatements(array($statement), $this->testGraph);

        $options = array(1);

        // assumption is that all given parameter will be returned
        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph, $options));

        // call hasMatchingStatement again to later see how many query cache container were created.
        $this->assertTrue($this->fixture->hasMatchingStatement($statement, $this->testGraph, $options));

        // check latest query cache container
        $this->assertEquals(
            array(
                array(
                    'graph_uris' => array($this->testGraph->getUri() => $this->testGraph->getUri()),
                    'query' => 'ASK FROM <http://localhost/Saft/TestGraph/> { ?s ?p ?o }',
                    'result' => true,
                    'triple_pattern' => array(
                        $this->testGraph->getUri() .
                        $this->separator .'*'. $this->separator .'*'. $this->separator .'*' =>
                            $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'.
                                $this->separator .'*'
                    )
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }

    /*
     * Tests for invalidateByGraphUri
     */
    public function testInvalidateByGraphUri()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = $this->queryFactory->createInstanceByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }'
        );

        $result = array(1, 2, 3);

        $this->fixture->saveResult($queryObject, $result);

        /**
         * Invalidate everything via a invalidateByGraphUri call
         */
        $this->fixture->invalidateByGraphUri($this->testGraph->getUri());

        /**
         * Check that everything was invalidated:
         * - graph URI entry
         * - pattern key entry
         * - query cache container itself
         */

        // graph URI entry
        $this->assertNull($this->fixture->getCache()->get($this->testGraph->getUri()));

        // pattern key entry
        $this->assertNull(
            $this->fixture->getCache()->get(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            )
        );

        // query cache container
        $this->assertNull($this->fixture->getCache()->get($queryObject->getQuery()));
    }

    /*
     * Tests for invalidateByQuery
     */
    public function testInvalidateByQuery()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = $this->queryFactory->createInstanceByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }'
        );

        $result = array(1, 2, 3);

        $this->fixture->saveResult($queryObject, $result);

        /**
         * Invalidate everything via a invalidateByQuery call
         */
        $this->fixture->invalidateByQuery($queryObject);

        /**
         * Check that everything was invalidated:
         * - graph URI entry
         * - pattern key entry
         * - query cache container itself
         */

        // graph URI entry
        $this->assertNull($this->fixture->getCache()->get($this->testGraph->getUri()));

        // pattern key entry
        $this->assertNull(
            $this->fixture->getCache()->get(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            )
        );

        // query cache container
        $this->assertNull($this->fixture->getCache()->get($queryObject->getQuery()));
    }

    /*
     * Tests for invalidateByTriplePattern
     */

    public function testInvalidateByTriplePatternGraph3VariablesAndUriGiven()
    {
        /**
         * First create test data and save it via saveResult
         */
        $queryObject = $this->queryFactory->createInstanceByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }'
        );

        $result = array(1, 2, 3);

        $this->fixture->saveResult($queryObject, $result);

        /**
         * Invalidate everything via a invalidateByTriplePattern call
         */
        $statementIterator = new ArrayStatementIteratorImpl(
            array(new StatementImpl(new AnyPatternImpl(), new AnyPatternImpl(), new AnyPatternImpl()))
        );

        $this->fixture->invalidateByTriplePattern($statementIterator, $this->testGraph->getUri());

        /**
         * Check that everything was invalidated:
         * - graph URI entry
         * - pattern key entry
         * - query cache container itself
         */

        // graph URI entry
        $this->assertNull($this->fixture->getCache()->get($this->testGraph->getUri()));

        // pattern key entry
        $this->assertNull(
            $this->fixture->getCache()->get(
                $this->testGraph->getUri() . '*'. $this->separator .'*'. $this->separator .'*'
            )
        );

        // query cache container
        $this->assertNull($this->fixture->getCache()->get($queryObject->getQuery()));
    }

    /*
     * Tests for query
     */

    public function testQuery()
    {
        $this->markTestSkipped("We need variables for this");
        // set basic store as successor
        $successor = new BasicTriplePatternStore(new NodeFactoryImpl());
        $this->fixture->setChainSuccessor($successor);

        // test data
        $query = 'SELECT * FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}';
        $options = array();

        $this->assertEquals(
            array(
                new StatementImpl(new AnyPatternImpl('?s'), new AnyPatternImpl('?p'), new AnyPatternImpl('?o')),
                null,
                $options
            ),
            $this->fixture->query($query, $options)
        );
    }

    public function testQueryNoSuccessor()
    {
        $this->setExpectedException('\Exception');

        $this->fixture->query('SELECT * FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}', array());
    }

    /*
     * Tests for saveResult
     */
    public function testSaveResultCacheEntries()
    {
        $queryObject = $this->queryFactory->createInstanceByQueryString(
            'SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE { ?s ?p ?o }'
        );

        $result = array(1, 2, 3);

        $this->fixture->saveResult($queryObject, $result);

        /**
         * check saved references between graph URIs (from query) and a array of query strings
         */
        $this->assertEquals(
            array($queryObject->getQuery() => $queryObject->getQuery()),
            $this->fixture->getCache()->get($this->testGraph->getUri())
        );

        /**
         * check saved references between triple pattern (from query) and a array of query strings
         */
        $this->assertEquals(
            array($queryObject->getQuery() => $queryObject->getQuery()),
            $this->fixture->getCache()->get(
                $this->testGraph->getUri() . $this->separator .'*'. $this->separator .'*'. $this->separator .'*'
            )
        );

        /**
         * check saved references between triple pattern (from query) and a array of query strings
         */
        $sep = $this->separator;
        $this->assertEquals(
            array(
                'graph_uris' => array(
                    $this->testGraph->getUri() => $this->testGraph->getUri()
                ),
                'triple_pattern' => array(
                    $this->testGraph->getUri() . $sep .'*'. $sep .'*'. $sep .'*' =>
                        $this->testGraph->getUri() . $sep .'*'. $sep .'*'. $sep .'*',
                ),
                'result' => $result,
                'query' => $queryObject->getQuery(),
            ),
            $this->fixture->getCache()->get($queryObject->getQuery())
        );

        /**
         * check, that upper query cache container was added to latestQueryCacheContainer during saveResult
         */
        $this->assertEquals(
            array(
                array(
                    'graph_uris' => array(
                        $this->testGraph->getUri() => $this->testGraph->getUri()
                    ),
                    'triple_pattern' => array(
                        $this->testGraph->getUri() . $sep .'*'. $sep .'*'. $sep .'*' =>
                            $this->testGraph->getUri() . $sep .'*'. $sep .'*'. $sep .'*',
                    ),
                    'result' => $result,
                    'query' => $queryObject->getQuery(),
                )
            ),
            $this->fixture->getLatestQueryCacheContainer()
        );
    }
}
