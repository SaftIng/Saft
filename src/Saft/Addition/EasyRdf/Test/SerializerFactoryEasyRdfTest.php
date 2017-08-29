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

namespace Saft\Addition\EasyRdf\Data\Test;

use Saft\Addition\EasyRdf\Data\SerializerFactoryEasyRdf;
use Saft\Data\Test\SerializerFactoryAbstractTest;

class SerializerFactoryEasyRdfTest extends SerializerFactoryAbstractTest
{
    /**
     * This list represents all serializations that are supported by the Serializers behind the ParserFactory
     * class to test.
     *
     * @var array
     */
    protected $availableSerializations = array(
        'n-triples',
        'rdf-json',
        'rdf-xml',
        'rdfa',
        'turtle'
    );

    /**
     * @return SerializerFactory
     */
    protected function newInstance()
    {
        return new SerializerFactoryEasyRdf();
    }
}
