<?php

namespace Saft\Skeleton\Test\Unit\PropertyHelper;

use Nette\Caching\Cache;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Skeleton\PropertyHelper\RequestHandler;
use Saft\Skeleton\Test\TestCase;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Query\QueryUtils;
use Saft\Store\BasicTriplePatternStore;

class RequestHandlerTest extends TestCase
{
    protected $cache;
    protected $storage;
    protected $store;

    public function setUp()
    {
        parent::setUp();

        // store
        $this->store = new BasicTriplePatternStore(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
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
                    new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s1'),
                    new NamedNodeImpl(new NodeUtils(), 'http://purl.org/dc/terms/title'),
                    new LiteralImpl(new NodeUtils(), 's1 dcterms title')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s2'),
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                    new LiteralImpl(new NodeUtils(), 's2 rdfs label')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s2'),
                    new NamedNodeImpl(new NodeUtils(), 'http://purl.org/dc/terms/title'),
                    new LiteralImpl(new NodeUtils(), 's2 dcterms title')
                ),
                new StatementImpl(
                    new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s2'),
                    new NamedNodeImpl(new NodeUtils(), 'http://purl.org/dc/terms/title'),
                    new LiteralImpl(new NodeUtils(), 's2 dcterms title - 2')
                )
            ),
            $this->testGraph
        );
    }

    /*
     * Tests for getAvailableCacheBackends
     */

    public function testGetAvailableCacheBackends()
    {
        $this->assertEquals(
            array('apc', 'filesystem', 'memcached', 'memory', 'mongodb', 'redis'),
            $this->fixture->getAvailableCacheBackends()
        );
    }

    /*
     * Tests for getAvailableTypes
     */

    public function testGetAvailableTypes()
    {
        $this->assertEquals(
            array('title'),
            $this->fixture->getAvailableTypes()
        );
    }

    /*
     * Tests for handle
     */

    public function testHandleActionCreateIndexEmptyStore()
    {
        // setup cache using memory
        $this->fixture->setupCache(array('name' => 'memory'));

        // set titlehelper
        $this->fixture->setType('title');

        $this->assertEquals(array(), $this->fixture->handle('createIndex'));
    }

    public function testHandleActionCreateIndexNoIndexInitialized()
    {
        $this->setExpectedException('Exception');

        $this->assertNull($this->fixture->handle('createIndex'));
    }

    public function testHandleActionFetchValuesEmptyPayload()
    {
        $this->fillStoreWithTestData();

        // setup cache using memory
        $this->fixture->setupCache(array('name' => 'memory'));

        // set titlehelper
        $this->fixture->setType('title');

        $this->assertTrue(0 < count($this->fixture->handle('createIndex')));

        $this->assertEquals(
            array(),
            $this->fixture->handle('fetchValues')
        );
    }

    public function testHandleUnknownAction()
    {
        $this->setExpectedException('Exception');

        $this->fixture->handle('unknown');
    }

    /*
     * Tests for setType
     */

    public function testSetTypeSetupCacheNotCalledBefore()
    {
        $this->setExpectedException('Exception');

        $this->fixture->setType('title');
    }

    public function testSetTypeUnknownType()
    {
        $this->setExpectedException('Exception');

        $this->fixture->setType('unknown');
    }

    /*
     * Tests for setupCache
     */

    public function testSetupCacheParameterConfigurationIsEmpty()
    {
        $this->setExpectedException('Exception');

        $this->fixture->setupCache(array());
    }

    public function testSetupCacheParameterConfigurationDoesNotHaveKeyNameSet()
    {
        $this->setExpectedException('Exception');

        $this->fixture->setupCache(array('foo' => 'bar'));
    }

    public function testSetupCacheUnknownNameGiven()
    {
        $this->setExpectedException('Exception');

        $this->fixture->setupCache(array('name' => 'unknown'));
    }
}
