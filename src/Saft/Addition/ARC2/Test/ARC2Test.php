<?php

namespace Saft\Addition\ARC2\Test;

use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Result\ResultFactoryImpl;
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
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                $this->config['arc2Config']
            );

            $this->fixture->dropGraph($this->testGraph);
            $this->fixture->createGraph($this->testGraph);

        } else {
            $this->markTestSkipped('Array arc2Config is not set in the test-config.yml.');
        }
    }

    /**
     *
     */
    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->emptyAllTables();
        }

        parent::tearDown();
    }
}
