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

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementFactoryImpl;

class ArrayStatementIteratorImplTest extends AbstractStatementIteratorTest
{
    public function createInstanceWithArray(array $statements): StatementIterator
    {
        return new ArrayStatementIteratorImpl($statements);
    }

    public function getStatementFactory(): StatementFactory
    {
        return new StatementFactoryImpl();
    }

    public function getNodeFactory(): NodeFactory
    {
        return new NodeFactoryImpl();
    }
}
