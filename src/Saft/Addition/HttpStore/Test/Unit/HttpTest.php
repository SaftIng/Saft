<?php

namespace Saft\Addition\HttpStore\Test\Unit;

use Curl\Curl;
use \Mockery;
use Saft\Addition\HttpStore\Store\Http;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;

class HttpTest extends StoreAbstractTest
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    public function setUp()
    {
        parent::setUp();

        /*
         * Load configuration
         */
        if (true === isset($this->configuration['httpConfig'])) {

            $this->fixture = new Http(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                array('queryUrl' => 'http://query/url')
            );

            $this->httpClient = \Mockery::mock('\Curl\Curl');
            $this->httpClient->shouldReceive('close');
            $this->fixture->setClient($this->httpClient);

        } else {
            $this->markTestSkipped('Array httpConfig is not set in the test-config.yml.');
        }
    }

    /*
     * Tests for openConnection
     */

    public function testOpenConnectionInvalidAuthUrl()
    {
        // We expect that authentication fails, because the auth url is not valid
        $this->setExpectedException('\Exception');

        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');

        $config = array('authUrl' => 'http://not existend');
        $client = new Http(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $config
        );
        $client->setClient($this->httpClient);
        $client->openConnection();
    }

    public function testOpenConnectionInvalidQueryUrl()
    {
        // We expect that openConnection fails, because the query URL is not valid
        $this->setExpectedException('\Exception');

        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');

        $config = array('queryUrl' => 'http://not existend');
        $client = new Http(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $config
        );
        $client->setClient($this->httpClient);
        $client->openConnection();
    }

    /**
     * Tests add and delete statements on default graph
     */
    public function testAddAndDeleteStatementsOnDefaultGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get')
            ->andReturn(
                json_encode(array(
                    'boolean' => false
                )),
                json_encode(array(
                    'boolean' => false
                )),
                json_encode(array(
                    'boolean' => true
                )),
                json_encode(array(
                    'boolean' => true
                )),
                json_encode(array(
                    'boolean' => true
                )),
                json_encode(array(
                    'boolean' => false
                ))
            );

        parent::testAddAndDeleteStatementsOnDefaultGraph();
    }

    /*
     * Tests for createGraph
     */

    public function testAddStatements()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testAddStatements();
    }

    public function testAddStatementsInvalidStatements()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post');

        parent::testAddStatementsInvalidStatements();
    }

    public function testAddStatementsLanguageTags()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                ))
            );

        parent::testAddStatementsLanguageTags();
    }

    public function testAddStatementsNoTriplesAndQuads()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testAddStatementsNoTriplesAndQuads();
    }

    public function testAddStatementsTriples()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testAddStatementsTriples();
    }

    public function testAddStatementsUseStatementGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testAddStatementsUseStatementGraph();
    }

    public function testAddStatementsWithArray()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testAddStatementsWithArray();
    }

    /*
     * Tests for createGraph
     */

    public function testCreateGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('g')),
                    'results' => array(
                        'bindings' => array()
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                ))
            );

        parent::testCreateGraph();
    }

    /*
     * Tests for dropGraph
     */

    public function testDropGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('g')),
                    'results' => array(
                        'bindings' => array()
                    )
                ))
            );

        parent::testDropGraph();
    }

    /*
     * Tests for deleteMatchingStatements
     */

    public function testDeleteMatchingStatements()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testDeleteMatchingStatements();
    }

    public function testDeleteMatchingStatementsQuadRecognition()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '1',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                ))
            );

        parent::testDeleteMatchingStatementsQuadRecognition();
    }

    public function testDeleteMatchingStatementsStatementsWithLiteral()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '2',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                ))
            );

        parent::testDeleteMatchingStatementsStatementsWithLiteral();
    }

    public function testDeleteMatchingStatementsUseStatementGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testDeleteMatchingStatementsUseStatementGraph();
    }

    public function testDeleteMatchingStatementsWithVariables()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                ),
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('s', 'p', 'o', 'g')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testDeleteMatchingStatementsWithVariables();
    }

    /*
     * Tests for getMatchingStatements
     */

    public function testGetGraphs()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array(
                        'vars' => array(
                            'g'
                        )
                    ),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri()
                                )
                            )
                        )
                    )
                ))
            );

        parent::testGetGraphs();
    }

    /*
     * Tests for getMatchingStatements
     */

    public function testGetMatchingStatements()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array(
                        'vars' => array(
                            'count'
                        )
                    ),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/',
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/',
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o/',
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri(),
                                ),
                            ),
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s/',
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p/',
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => 'test literal',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri(),
                                ),
                            ),
                        )
                    )
                ))
            );

        parent::testGetMatchingStatements();
    }

    public function testGetMatchingStatementsReturnType()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testGetMatchingStatementsReturnType();
    }

    public function testGetMatchingStatementsEmptyGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array('bindings' => array())
                ))
            );

        parent::testGetMatchingStatementsEmptyGraph();
    }

    public function testGetMatchingStatementsCheckGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array(
                        'vars' => array(
                            'count'
                        )
                    ),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s',
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p',
                                ),
                                'o' => array(
                                    'type' => 'uri',
                                    'value' => 'http://o',
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => $this->testGraph->getUri(),
                                ),
                            ),
                        )
                    )
                ))
            );

        parent::testGetMatchingStatementsCheckGraph();
    }

    public function testGetMatchingStatementsCheckForTriples()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array(
                        'vars' => array(
                            'count'
                        )
                    ),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '1',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                ),
                            ),
                        )
                    )
                )),
                json_encode(array(
                    'head' => array(
                        'vars' => array(
                            's', 'g'
                        )
                    ),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => '1',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                ),
                                'g' => array(
                                    'type' => 'uri',
                                    'value' => 'http://graph/'
                                ),
                            ),
                        )
                    )
                ))
            );

        parent::testGetMatchingStatementsCheckForTriples();
    }

    public function testGetMatchingStatementsCheckForTriplesDefaultGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array(
                        'vars' => array(
                            'count'
                        )
                    ),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '1',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                ),
                            ),
                        )
                    )
                )),
                json_encode(array(
                    'head' => array(
                        'vars' => array(
                            's'
                        )
                    ),
                    'results' => array(
                        'bindings' => array(
                            array(
                                's' => array(
                                    'type' => 'uri',
                                    'value' => 'http://s'
                                ),
                                'p' => array(
                                    'type' => 'uri',
                                    'value' => 'http://p'
                                ),
                                'o' => array(
                                    'type' => 'typed-literal',
                                    'value' => '1',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                ),
                            ),
                        )
                    )
                ))
            );

        parent::testGetMatchingStatementsCheckForTriplesDefaultGraph();
    }

    public function testGetMatchingStatementsFromAnyGraph()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('post')
            ->andReturn(json_encode(array(
                'head' => array(
                    'vars' => array(
                        's', 'p', 'o', 'g'
                    )
                ),
                'results' => array(
                    'bindings' => array(
                        array(
                            's' => array(
                                'type' => 'uri',
                                'value' => 'http://s/',
                            ),
                            'p' => array(
                                'type' => 'uri',
                                'value' => 'http://p/',
                            ),
                            'o' => array(
                                'type' => 'uri',
                                'value' => 'http://o1/',
                            ),
                            'g' => array(
                                'type' => 'uri',
                                'value' => 'http://graph/a',
                            ),
                        ),
                        array(
                            's' => array(
                                'type' => 'uri',
                                'value' => 'http://s/',
                            ),
                            'p' => array(
                                'type' => 'uri',
                                'value' => 'http://p/',
                            ),
                            'o' => array(
                                'type' => 'uri',
                                'value' => 'http://o2/',
                            ),
                            'g' => array(
                                'type' => 'uri',
                                'value' => 'http://graph/b',
                            ),
                        ),
                    )
                )
            )));
        $this->httpClient->shouldReceive('get');

        parent::testGetMatchingStatementsFromAnyGraph();
    }

    /*
     * Tests for getRights
     */

    public function testGetRights()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array()),
                    'results' => array('bindings' => array())
                ))
            );
        $this->httpClient->shouldReceive('get')
            ->andReturn(
                'Error with graphUpdate', // provokes raising an exception
                json_encode(array()),
                'Error with tripleUpdate' // provokes raising an exception
            );

        $config = array('queryUrl' => 'http://dbpedia.org/sparql');

        $fixture = new Http(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $config
        );
        $fixture->setClient($this->httpClient);

        $this->assertEquals(
            array(
                'graphUpdate' => false,
                'tripleQuerying' => true,
                'tripleUpdate' => false,
            ),
            $fixture->getRights()
        );
    }


    public function testGetStoreDescription()
    {
        $this->httpClient->shouldReceive('setHeader');
        $this->httpClient->shouldReceive('get');

        parent::testGetStoreDescription();
    }

    /*
     * Tests for hasMatchingStatement
     */

    public function testHasMatchingStatement()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);
        $this->httpClient->shouldReceive('get')
            ->andReturn(json_encode(array(
                'boolean' => true
            )));

        parent::testHasMatchingStatement();
    }

    public function testHasMatchingStatementEmptyGraph()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);
        $this->httpClient->shouldReceive('get')
            ->andReturn(json_encode(array(
                'boolean' => false
            )));

        parent::testHasMatchingStatementEmptyGraph();
    }

    public function testHasMatchingStatementOnlyVariables()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);
        $this->httpClient->shouldReceive('get')
            ->andReturn(json_encode(array(
                'boolean' => false
            )));

        parent::testHasMatchingStatementOnlyVariables();
    }

    public function testHasMatchingStatementTripleRecognition()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);
        $this->httpClient->shouldReceive('get')
            ->andReturn(
                json_encode(array(
                    'boolean' => false
                ))
            );
        $this->httpClient->shouldReceive('post');

        parent::testHasMatchingStatementTripleRecognition();
    }

    /*
     * Tests for query
     */

    public function testQuery()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);

        $this->httpClient
            ->shouldReceive('get')
            ->times(3)
            ->andReturn(
                json_encode(array())
            );

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->andReturn(json_encode(array(
                'head' => array(
                    'vars' => array(
                        's', 'o'
                    )
                ),
                'results' => array(
                    'bindings' => array(
                        array(
                            's' => array(
                                'type' => 'uri',
                                'value' => 'http://s/',
                            ),
                            'o' => array(
                                'type' => 'uri',
                                'value' => 'http://o/',
                            ),
                        ),
                        array(
                            's' => array(
                                'type' => 'uri',
                                'value' => 'http://s/',
                            ),
                            'o' => array(
                                'type' => 'literal',
                                'value' => 'foobar',
                                'xml:lang' => 'en'
                            ),
                        ),
                        array(
                            's' => array(
                                'type' => 'uri',
                                'value' => 'http://s/',
                            ),
                            'o' => array(
                                'type' => 'typed-literal',
                                'value' => '42',
                                'datatype' => 'http://www.w3.org/2001/XMLSchema#string'
                            ),
                        )
                    )
                )
            )));

        parent::testQuery();
    }

    public function testQueryAddAndQueryStatementsDefaultGraph()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);

        $this->httpClient
            ->shouldReceive('get')
            ->andReturn(json_encode(array()));

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->andReturn(json_encode(array(
                'head' => array(
                    'vars' => array(
                        'p', 'o'
                    )
                ),
                'results' => array(
                    'bindings' => array(
                        array(
                            'p' => array(
                                'type' => 'uri',
                                'value' => 'http://example.org/b'
                            ),
                            'o' => array(
                                'type' => 'uri',
                                'value' => 'http://example.org/c'
                            )
                        )
                    )
                )
            )));

        parent::testQueryAddAndQueryStatementsDefaultGraph();
    }

    public function testQueryAsk()
    {
        $this->httpClient->shouldReceive('setHeader')->times(8);

        $this->httpClient
            ->shouldReceive('get')
            ->times(3)
            ->andReturn(
                json_encode(array()),
                json_encode(array()),
                json_encode(array(
                    'boolean' => true
                ))
            );

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->andReturn(json_encode(array(
                'head' => array(
                    'vars' => array(
                        's', 'p', 'o', 'g'
                    )
                ),
                'results' => array(
                    'bindings' => array(
                    )
                )
            )));

        parent::testQueryAsk();
    }

    public function testQueryConstruct()
    {
        $this->httpClient->shouldReceive('setHeader')->times(8);

        $this->httpClient
            ->shouldReceive('get')
            ->times(3)
            ->andReturn(
                json_encode(array()),
                json_encode(array()),
                json_encode(array(
                    'boolean' => true
                ))
            );

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->andReturn(json_encode(array(
                'head' => array(
                    'vars' => array(
                        's', 'p', 'o'
                    )
                ),
                'results' => array(
                    'distinct' => false,
                    'ordered' => true,
                    'bindings' => array(
                        array(
                            's' => array('type' => 'uri', 'value' => 'http://saft/testquad/p1'),
                            'p' => array('type' => 'uri', 'value' => 'http://saft/testquad/s1'),
                            'o' => array('type' => 'uri', 'value' => 'http://saft/testquad/o1')
                        ),
                        array(
                            's' => array('type' => 'uri', 'value' => 'http://saft/testtriple/p2'),
                            'p' => array('type' => 'uri', 'value' => 'http://saft/testtriple/s2'),
                            'o' => array('type' => 'uri', 'value' => 'http://saft/testtriple/o2')
                        )
                    )
                )
            )));

        parent::testQueryConstruct();
    }

    public function testQueryConstructEmptyGraph()
    {
        $this->httpClient->shouldReceive('setHeader')->times(8);

        $this->httpClient
            ->shouldReceive('get')
            ->times(3)
            ->andReturn(
                json_encode(array()),
                json_encode(array()),
                json_encode(array(
                    'boolean' => true
                ))
            );

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->andReturn(json_encode(array(
                'head' => array(
                    'vars' => array(
                        's', 'p', 'o', 'g'
                    )
                ),
                'results' => array(
                    'bindings' => array(
                    )
                )
            )));

        parent::testQueryConstructEmptyGraph();
    }

    public function testQueryEmptyResult()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);
        $this->httpClient->shouldReceive('get')->times(1);
        $this->httpClient->shouldReceive('post')->times(1)
            ->andReturn(json_encode(array(
                'head' => array(
                    'vars' => array(
                        's', 'p', 'o'
                    )
                ),
                'results' => array(
                    'bindings' => array(
                    )
                )
            )));

        parent::testQueryEmptyResult();
    }

    public function testQueryDeleteMultipleStatementsVariablePatterns()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);
        $this->httpClient->shouldReceive('get')
            ->andReturn(json_encode(array(
                'head' => array('vars' => array('count')),
                'results' => array(
                    'bindings' => array(
                        array(

                        )
                    )
                )
            )));

        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '2',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                ))
            );

        parent::testQueryDeleteMultipleStatementsVariablePatterns();
    }

    public function testQueryDeleteMultipleStatementsStatementsWithLiteral()
    {
        $this->httpClient->shouldReceive('setHeader')->times(2);
        $this->httpClient->shouldReceive('get')
            ->andReturn(json_encode(array(
                'head' => array('vars' => array('count')),
                'results' => array(
                    'bindings' => array(
                        array(

                        )
                    )
                )
            )));

        $this->httpClient->shouldReceive('post')
            ->andReturn(
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '2',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                )),
                json_encode(array(
                    'head' => array('vars' => array('count')),
                    'results' => array(
                        'bindings' => array(
                            array(
                                'count' => array(
                                    'type' => 'typed-literal',
                                    'value' => '0',
                                    'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                                )
                            )
                        )
                    )
                ))
            );

        parent::testQueryDeleteMultipleStatementsStatementsWithLiteral();
    }
}
