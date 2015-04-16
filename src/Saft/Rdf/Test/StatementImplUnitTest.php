<?php
namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;

class StatementImplUnitTest extends StatementAbstractTest
{

    public function newLiteralInstance($value, $lang = null, $datatype = null)
    {
        return new LiteralImpl($value, $lang, $datatype);
    }

    public function newNamedNodeInstance($uri)
    {
        return new NamedNodeImpl($uri);
    }

    public function newVariableInstance($value)
    {
        return new VariableImpl($value);
    }

    public function newBlankNodeInstance($id)
    {
        return new BlankNodeImpl($id);
    }

    public function newInstance($subject, $predicate, $object, $graph = null)
    {
        return new StatementImpl($subject, $predicate, $object, $graph);
    }
}
