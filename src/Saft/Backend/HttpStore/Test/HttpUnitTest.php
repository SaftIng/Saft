<?php
namespace Saft\Backend\HttpStore\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Symfony\Component\Yaml\Parser;
use Saft\Cache\Cache;
use Saft\Backend\HttpStore\Store\Http;

class HttpUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cache
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
    protected $testGrap;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

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

        // if httpConfig array is set
        if (true === isset($this->config['httpConfig'])) {
            $this->fixture = new Http($this->config['httpConfig']);

        // if standard store is set to http
        } elseif ('http' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new Http(
                $this->config['configuration']['standardStore']
            );

        // no configuration is available, dont execute tests
        } else {
            $this->markTestSkipped('Array httpConfig is not set in the config.yml.');
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
     * Tests addGraph
     */

    public function testAddGraph()
    {
        $this->fixture->dropGraph($this->testGraph);

        $this->assertFalse($this->fixture->isGraphAvailable($this->testGraph));

        $this->fixture->addGraph($this->testGraph);

        $this->assertTrue($this->fixture->isGraphAvailable($this->testGraph));
    }

    /**
     * Tests clearGraph
     */

    public function testClearGraph()
    {
        // remove all triples from the test graph
        $this->fixture->query('CLEAR GRAPH <' . $this->testGraph->getUri() . '>');

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
        $this->assertTrue($this->fixture->addStatements($statements, $this->testGraph));

        // graph has two entries now
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));
        
        $this->fixture->clearGraph($this->testGraph);
        
        // check number of triples again
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));
    }

    /**
     * Tests dropGraph
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
     * Tests existence (simple)
     */
    public function testExistence()
    {
        $this->assertTrue(class_exists('\Saft\Backend\HttpStore\Store\Http'));
    }

    /**
     * Tests getAvailableGraphUris
     */

    public function testGetAvailableGraphUris()
    {
        // assumption here is that the SPARQL endpoint contains at least one graph.

        $this->assertTrue(0 < count($this->fixture->getAvailableGraphs()));
    }

    /**
     * Tests getTripleCount
     */

    public function testGetTripleCount()
    {
        // graph is empty
        $this->assertEquals(0, $this->fixture->getTripleCount($this->testGraph));

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

        // graph has to contain 3 triples
        $this->assertEquals(2, $this->fixture->getTripleCount($this->testGraph));
    }
}
