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
    public function createStatement(Node $subject, Node $predicate, Node $object, Node $graph = null)
    {
        return new StatementImpl($subject, $predicate, $object, $graph);
    }
}
