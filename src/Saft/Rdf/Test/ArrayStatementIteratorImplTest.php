<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;

class ArrayStatementIteratorImplTest extends StatementIteratorAbstractTest
{
    public function createInstanceWithArray(array $statements)
    {
        return new ArrayStatementIteratorImpl($statements);
    }
}
