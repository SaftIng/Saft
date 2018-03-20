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

namespace Saft\Sparql\Test\Query;

use Saft\Rdf\RdfHelpers;
use Saft\Sparql\Query\QueryFactoryImpl;

class QueryFactoryImplTest extends AbstractQueryFactoryTest
{
    /**
     * Returns subject to test.
     *
     * @return QueryFactory
     */
    public function newInstance()
    {
        return new QueryFactoryImpl(new RdfHelpers());
    }
}
