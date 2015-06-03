<?php

namespace Saft\Backend\Virtuoso\Test;

use Saft\Backend\Virtuoso\Store\Virtuoso;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;
use Symfony\Component\Yaml\Parser;

class VirtuosoTest extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadTestConfiguration();

        if (true === isset($this->config['virtuosoConfig'])) {
            $this->fixture = new Virtuoso(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                $this->config['virtuosoConfig']
            );

        } else {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
        }
    }

    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->dropGraph($this->testGraph);
        }

        parent::tearDown();
    }
}
