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

use Saft\Sparql\Result\ResultFactory;
use Saft\Sparql\Result\ResultFactoryImpl;

class ResultFactoryImplTest extends AbstractResultFactoryTest
{
    protected function getInstance(): ResultFactory
    {
        return new ResultFactoryImpl();
    }
}
