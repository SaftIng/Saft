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

namespace Saft\Sparql\Test\Result;

use Saft\Sparql\Result\StatementSetResultImpl;

class StatementSetResultImplTest extends StatementSetResultAbstractTest
{
    /**
     * @return StatementResult
     */
    public function newInstance($list)
    {
        return new StatementSetResultImpl($list);
    }
}
