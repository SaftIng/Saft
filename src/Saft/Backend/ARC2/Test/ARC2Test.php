<?php

namespace Saft\Backend\ARC2\Test;

use Saft\Backend\ARC2\Store\ARC2;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Rdf\StatementImpl;
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
        } elseif (true === isset($this->config['configuration']['standardStore'])) {
            $this->fixture = new ARC2(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
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
            $this->fixture->emptyAllTables();
        }

        parent::tearDown();
    }
}
