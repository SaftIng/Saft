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

namespace Saft\Addition\EasyRdf\Test;

use Saft\Addition\EasyRdf\Data\ParserFactoryEasyRdf;
use Saft\Data\Test\AbstractParserFactoryTest;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;

class ParserFactoryEasyRdfTest extends AbstractParserFactoryTest
{
    /**
     * This list represents all serializations that are supported by the Parsers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = [
        'n-triples',
        'rdf-json',
        'rdf-xml',
        'rdfa',
        'turtle',
    ];

    /**
     * @return ParserFactory
     */
    protected function newInstance()
    {
        return new ParserFactoryEasyRdf(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            new RdfHelpers()
        );
    }
}
