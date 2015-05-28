<?php

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
     * Tests createIteratorFromArray
     */

    public function testCreateIteratorFromArrayArrayGiven()
    {
        $this->fixture = $this->newInstance();
        $parameter = array();

        $this->assertClassOfInstanceImplements(
            $this->fixture->createIteratorFromArray($parameter),
            'Saft\Rdf\StatementIterator'
        );
    }

    public function testCreateIteratorFromArrayInvalidParameterGiven()
    {
        $this->setExpectedException('\Exception');

        $parameter = array('invalid parameter');
        $this->newInstance()->createIteratorFromArray($parameter);
    }
}
