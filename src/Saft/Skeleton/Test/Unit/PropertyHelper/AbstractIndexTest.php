<?php

namespace Saft\Skeleton\Test\Unit\PropertyHelper;

use Zend\Cache\StorageFactory;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Skeleton\Test\TestCase;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;

abstract class AbstractIndexTest extends TestCase
{
    protected $cache;
    protected $store;

    public function setUp()
    {
        parent::setUp();

        // cache environment
        $this->cache = StorageFactory::factory(
            array(
                'adapter' => array(
                    'name' => 'memory',
                    'options' => array(
                        'namespace' => $this->testGraph->getUri()
                    )
        )));

        // store
        $this->store = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );
    }

    public function fillStoreWithTestData()
    {
        // add test data to store
        $this->store->addStatements(
            array(
                new StatementImpl(
                    new NamedNodeImpl('http://saft/test/s1'),
                    new NamedNodeImpl('http://purl.org/dc/terms/title'),
                    new LiteralImpl('s1 dcterms title')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://saft/test/s2'),
                    new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl('s2 rdfs label')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://saft/test/s2'),
                    new NamedNodeImpl('http://purl.org/dc/terms/title'),
                    new LiteralImpl('s2 dcterms title')
                ),
                new StatementImpl(
                    new NamedNodeImpl('http://saft/test/s2'),
                    new NamedNodeImpl('http://purl.org/dc/terms/title'),
                    new LiteralImpl('s2 dcterms title - 2')
                )
            ),
            $this->testGraph
        );
    }

    /*
     * Tests for createIndex
     */

    public function testCreateIndex()
    {
        $this->fillStoreWithTestData();

        // create property index
        $this->assertEqualsArrays(
            array(
                'http://saft/test/s1' => array(
                    'titles' => array(
                        array(
                            'uri' => 'http://purl.org/dc/terms/title',
                            'title' => 's1 dcterms title'
                        )
                    )
                ),
                'http://saft/test/s2' => array(
                    'titles' => array(
                        array(
                            'uri' => 'http://www.w3.org/2000/01/rdf-schema#label',
                            'title' => 's2 rdfs label'
                        ),
                        array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title'),
                        array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title - 2'),
                    )
                )
            ),
            $this->fixture->createIndex()
        );

        // test created cache entries
        $this->assertEqualsArrays(
            array(
                'titles' => array(
                    array(
                        'uri' => 'http://purl.org/dc/terms/title',
                        'title' => 's1 dcterms title'
                    )
                )
            ),
            unserialize($this->cache->getItem(md5('http://saft/test/s1')))
        );

        $resultArray = unserialize($this->cache->getItem(md5('http://saft/test/s2')));

        $this->assertTrue(isset($resultArray['titles']));
        $this->assertEquals(
            array(
                array('uri' => 'http://www.w3.org/2000/01/rdf-schema#label', 'title' => 's2 rdfs label'),
                array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title - 2'),
                array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title')
            ),
            array_values($resultArray['titles'])
        );
    }

    public function testCreateIndexMultipleProperties()
    {
        $this->fillStoreWithTestData();

        $this->fixture = $this->getMockForAbstractClass(
            '\Saft\Skeleton\PropertyHelper\AbstractIndex',
            array(
                $this->cache,
                $this->store,
                $this->testGraph,
                array(
                    'http://a',
                    'http://b',
                )
            )
        );

        // create property index
        $this->assertEqualsArrays(
            array(
                'http://saft/test/s1' => array(
                    'titles' => array(
                        array(
                            'uri' => 'http://purl.org/dc/terms/title',
                            'title' => 's1 dcterms title'
                        )
                    )
                ),
                'http://saft/test/s2' => array(
                    'titles' => array(
                        array(
                            'uri' => 'http://www.w3.org/2000/01/rdf-schema#label',
                            'title' => 's2 rdfs label'
                        ),
                        array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title'),
                        array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title - 2'),
                    )
                )
            ),
            $this->fixture->createIndex()
        );

        // test created cache entries
        $this->assertEqualsArrays(
            array(
                'titles' => array(
                    array(
                        'uri' => 'http://purl.org/dc/terms/title',
                        'title' => 's1 dcterms title'
                    )
                )
            ),
            unserialize($this->cache->getItem(md5('http://saft/test/s1')))
        );

        $resultArray = unserialize($this->cache->getItem(md5('http://saft/test/s2')));

        $this->assertTrue(isset($resultArray['titles']));
        $this->assertEqualsArrays(
            array(
                array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title - 2'),
                array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title'),
                array('uri' => 'http://www.w3.org/2000/01/rdf-schema#label', 'title' => 's2 rdfs label')
            ),
            array_values($resultArray['titles'])
        );
    }

    /*
     * Tests fetchValues
     */

    public function testFetchValues()
    {
        $this->fillStoreWithTestData();

        // create property index
        $this->fixture->createIndex();

        // test created cache entries
        $this->assertEqualsArrays(
            array(
                'http://saft/test/s1' => 's1 dcterms title'
            ),
            $this->fixture->fetchValues(
                array(
                    'http://saft/test/s1'
                )
            )
        );
    }

    public function testFetchValuesNotAvailableUri()
    {
        $this->fillStoreWithTestData();

        // create property index
        $this->fixture->createIndex();

        // test created cache entries
        $this->assertEqualsArrays(
            array(
                'http://not_available' => ''
            ),
            $this->fixture->fetchValues(
                array(
                    'http://not_available'
                )
            )
        );
    }

    public function testFetchValuesEmptyUriList()
    {
        $this->fillStoreWithTestData();

        // create property index
        $this->fixture->createIndex();

        // test created cache entries
        $this->assertEqualsArrays(
            array(),
            $this->fixture->fetchValues(
                array()
            )
        );
    }
}
