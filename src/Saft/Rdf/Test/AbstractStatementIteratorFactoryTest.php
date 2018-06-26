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

use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;

abstract class AbstractStatementIteratorFactoryTest extends TestCase
{
    /**
     * @return StatementIteratorFactory
     */
    abstract public function getInstance(): StatementIteratorFactory;

    /*
     * Tests createStatementIteratorFromArray
     */

    public function testCreateStatementIteratorFromArrayArrayGiven()
    {
        $this->assertTrue(
            $this->getInstance()->createStatementIteratorFromArray([]) instanceof StatementIterator
        );
    }

    public function testCreateStatementIteratorFromArrayInvalidParameterGiven()
    {
        $this->expectException('\Exception');

        $parameter = ['invalid parameter'];
        $this->getInstance()->createStatementIteratorFromArray($parameter);
    }
}
