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
