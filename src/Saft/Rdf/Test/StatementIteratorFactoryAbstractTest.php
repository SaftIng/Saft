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

namespace Saft\Rdf\Test;

use Saft\Rdf\StatementIteratorFactory;
use Saft\Test\TestCase;

abstract class StatementIteratorFactoryAbstractTest extends TestCase
{
    /**
     * @return StatementIteratorFactory
     */
    abstract public function newInstance();

    /*
     * Tests createStatementIteratorFromArray
     */

    public function testCreateStatementIteratorFromArrayArrayGiven()
    {
        $this->fixture = $this->newInstance();
        $parameter = array();

        $this->assertClassOfInstanceImplements(
            $this->fixture->createStatementIteratorFromArray($parameter),
            'Saft\Rdf\StatementIterator'
        );
    }

    public function testCreateStatementIteratorFromArrayInvalidParameterGiven()
    {
        $this->setExpectedException('\Exception');

        $parameter = array('invalid parameter');
        $this->newInstance()->createStatementIteratorFromArray($parameter);
    }
}
