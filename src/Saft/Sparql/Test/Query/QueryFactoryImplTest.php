<?php

namespace Saft\Sparql\Test\Query;

use Saft\Rdf\NodeUtils;
use Saft\Sparql\Query\QueryUtils;
use Saft\Sparql\Query\QueryFactoryImpl;

class QueryFactoryImplTest extends QueryFactoryAbstractTest
{
    /**
     * Returns subject to test.
     *
     * @return QueryFactory
     */
    public function newInstance()
    {
        return new QueryFactoryImpl(new NodeUtils(), new QueryUtils());
    }
}
