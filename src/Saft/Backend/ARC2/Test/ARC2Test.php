<?php

namespace Saft\Backend\ARC2\Test;

use Saft\Backend\ARC2\Store\ARC2;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Store\Test\StoreAbstractTest;
use Symfony\Component\Yaml\Parser;

class ARC2Test extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        if (true === isset($this->config['arc2Config'])) {
            $this->fixture = new ARC2(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                $this->config['arc2Config']
            );
        } elseif (true === isset($this->config['configuration']['standardStore'])) {
            $this->fixture = new ARC2(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped('Array arc2Config is not set in the config.yml.');
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

    public function testARC2Query()
    {
        /*
        $this->fixture->query('INSERT INTO <'. $this->testGraph->getUri() .'> {
            <http://example/book1> <http://example/title> "A new book" ;
                                   <http://example/creator> "A.N.Other" .
        }');

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
        // $this->fixture->addStatements($statements, $this->testGraph);

        // $this->fixture->query('SELECT ?s ?p ?o FROM <'. $this->testGraph->getUri() .'> WHERE {?s ?p ?o.}');

        $res = $this->fixture->query('SELECT DISTINCT ?g WHERE { GRAPH ?g {?s ?p ?o.} }');
        foreach ($res as $key => $value) {
            var_dump($value);
        }*/
    }
}
