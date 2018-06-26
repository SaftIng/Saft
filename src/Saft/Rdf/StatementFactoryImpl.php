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

namespace Saft\Rdf;

class StatementFactoryImpl implements StatementFactory
{
    /**
     * Creates a new statement, either a 3-tuple or 4-tuple.
     *
     * @param Node $subject   subject of the statement
     * @param Node $predicate predicate of the statement
     * @param Node $object    object of the statement
     * @param Node $graph     Graph of the statement. (optional)
     *
     * @return Statement instance of Statement
     */
    public function createStatement(Node $subject, Node $predicate, Node $object, Node $graph = \null): Statement
    {
        return new StatementImpl($subject, $predicate, $object, $graph);
    }
}
