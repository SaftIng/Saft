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

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementImpl;

class StatementImplTest extends StatementAbstractTest
{
    public function newAnyPatternInstance($value)
    {
        return new AnyPatternImpl($value);
    }

    public function newBlankNodeInstance($blankId)
    {
        return new BlankNodeImpl($blankId);
    }

    public function newInstance($subject, $predicate, $object, $graph = null)
    {
        return new StatementImpl($subject, $predicate, $object, $graph);
    }

    public function newLiteralInstance($value, $datatype = null, $lang = null)
    {
        return new LiteralImpl(new RdfHelpers(), $value, $datatype, $lang);
    }

    public function newNamedNodeInstance($uri)
    {
        return new NamedNodeImpl(new RdfHelpers(), $uri);
    }
}
