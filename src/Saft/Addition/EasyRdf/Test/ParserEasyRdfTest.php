<?php

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\ParserEasyRdf;
use Saft\Addition\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Data\Test\ParserAbstractTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;

class ParserEasyRdfTest extends ParserAbstractTest
{
    protected $factory;

    public function __construct()
    {
        $this->factory = new ParserFactoryEasyRdf(
            new NodeFactoryImpl(new NodeUtils()),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new NodeUtils()
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
