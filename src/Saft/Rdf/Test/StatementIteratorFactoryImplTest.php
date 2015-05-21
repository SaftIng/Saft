<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Test\TestCase;

class StatementIteratorFactoryImplTest extends StatementIteratorFactoryAbstractTest
{
    /**
     * @return StatementIteratorFactory
     */
    public function newInstance()
    {
        return new StatementIteratorFactoryImpl();
    }
}
