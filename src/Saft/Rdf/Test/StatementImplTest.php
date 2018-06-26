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

use Saft\Rdf\AnyPattern;
use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\BlankNode;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\Literal;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\Node;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;

class StatementImplTest extends AbstractStatementTest
{
    public function getAnyPatternInstance($value): AnyPattern
    {
        return new AnyPatternImpl($value);
    }

    public function getBlankNodeInstance(string $blankId): BlankNode
    {
        return new BlankNodeImpl($blankId);
    }

    public function getInstance(Node $subject, Node $predicate, Node $object, $graph = null): Statement
    {
        return new StatementImpl($subject, $predicate, $object, $graph);
    }

    public function getLiteralInstance(string $value, $datatype = null, $lang = null): Literal
    {
        return new LiteralImpl($value, $datatype, $lang);
    }

    public function getNamedNodeInstance(string $uri): NamedNode
    {
        return new NamedNodeImpl($uri);
    }
}
