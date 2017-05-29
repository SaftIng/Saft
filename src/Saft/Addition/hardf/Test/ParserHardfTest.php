<?php

namespace Saft\Addition\hardf\Test;

use Saft\Addition\hardf\Data\ParserHardf;
use Saft\Addition\hardf\Data\ParserFactoryHardf;
use Saft\Data\Test\ParserAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;

class ParserHardfTest extends ParserAbstractTest
{
    protected $factory;

    public function __construct()
    {
        $this->factory = new ParserFactoryHardf(
            new NodeFactoryImpl(new RdfHelpers()),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers()
        );
    }

    /**
     * @return Parser
     */
    protected function newInstance($serialization)
    {
        return $this->factory->createParserFor($serialization);
    }
}
