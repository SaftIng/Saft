<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Addition\hardf\Test\Data;

use Saft\Addition\hardf\Data\ParserFactoryHardf;
use Saft\Data\ParserFactory;
use Saft\Data\Test\AbstractParserFactoryTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;

class ParserFactoryHardfTest extends AbstractParserFactoryTest
{
    /**
     * This list represents all serializations that are supported by the Parsers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = [
        'n-triples',
        'n-quads',
        'turtle',
    ];

    /**
     * @return ParserFactory
     */
    protected function getInstance(): ParserFactory
    {
        return new ParserFactoryHardf(
            $this->nodeFactory,
            $this->statementFactory,
            $this->statementIteratorFactory,
            $this->rdfHelpers
        );
    }
}
