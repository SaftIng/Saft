<?php

namespace Saft\Backend\Virtuoso\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\AnyPatternImpl;
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
            $this->fixture = new Virtuoso(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                $this->config['virtuosoConfig']
            );
        } elseif (true === isset($this->config['configuration']['standardStore'])
            && 'virtuoso' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new Virtuoso(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
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
     * Tests getTripleCount
     */

    public function testGetTripleCount()
    {
        $anyStatement = new StatementImpl(
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl(),
            new AnyPatternImpl()
        );

        // graph is empty
        $statements = $this->fixture->getMatchingStatements($anyStatement, $this->testGraph);
        $this->assertCountStatementIterator(0, $statements);

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
