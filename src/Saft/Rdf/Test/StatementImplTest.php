<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
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

    public function newLiteralInstance($value, $lang = null, $datatype = null)
    {
        return new LiteralImpl($value, $lang, $datatype);
    }

    public function newNamedNodeInstance($uri)
    {
        return new NamedNodeImpl($uri);
    }
}
