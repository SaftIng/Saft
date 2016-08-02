<?php

namespace Saft\Skeleton\Test\Integration\PropertyHelper;

use Nette\Caching\Cache;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\MemcachedStorage;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Skeleton\PropertyHelper\RequestHandler;
use Saft\Skeleton\Test\TestCase;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;

abstract class AbstractRequestHandlerTest extends TestCase
{
    protected $cache;
    protected $storage;
    protected $store;

    public function setUp()
    {
        parent::setUp();

        // store
        $this->store = new BasicTriplePatternStore(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new StatementIteratorFactoryImpl()
        );

        $this->fixture = new RequestHandler($this->store, $this->testGraph);
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
     * Tests for handle
     */

    public function testHandleActionCreateIndex()
    {
        $this->fillStoreWithTestData();

        // setup cache
        $this->setupCache();

        // set titlehelper
        $this->fixture->setType('title');

        $this->assertEquals(
            array(
                'http://saft/test/s1' => array(
                    'titles' => array(array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's1 dcterms title'))
                ),
                'http://saft/test/s2' => array(
                    'titles' => array(
                        array('uri' => 'http://www.w3.org/2000/01/rdf-schema#label', 'title' => 's2 rdfs label'),
                        array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title'),
                        array('uri' => 'http://purl.org/dc/terms/title', 'title' => 's2 dcterms title - 2'),
                    )
                )
            ),
            $this->fixture->handle('createIndex')
        );
    }

    public function testHandleActionFetchValues()
    {
        $this->fillStoreWithTestData();

        // setup cache using memory
        $this->setupCache();

        // set titlehelper
        $this->fixture->setType('title');

        $this->assertTrue(0 < count($this->fixture->handle('createIndex')));

        $this->assertEquals(
            array('http://saft/test/s2' => 's2 rdfs label'),
            $this->fixture->handle('fetchValues', array('http://saft/test/s2'))
        );
    }

    /*
     * Tests for setupCache
     */

    public function testSetupCache()
    {
        $this->assertNull($this->setupCache());
    }
}
