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

use Saft\Sparql\Query\AbstractQuery;

/**
 * This class is a child of AbstractQuery and there for testing. We avoiding faulty abstract class
 * handling in PHPUnit that way. Problem was, that having an abstract class with constructor prevents you from mocking
 * it, because it was not possible to pass arguments to the constructor.
 */
class AbstractQueryChild extends AbstractQuery
{
    /*
     * The following methods are only dummys to enable instantiation of the class.
     */
    public function getQueryParts()
    {
    }

    public function isAskQuery()
    {
    }

    public function isConstructQuery()
    {
    }

    public function isDescribeQuery()
    {
    }

    public function isGraphQuery()
    {
    }

    public function isSelectQuery()
    {
    }

    public function isUpdateQuery()
    {
    }
}
