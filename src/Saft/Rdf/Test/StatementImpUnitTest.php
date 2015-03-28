<?php
namespace Saft\Rdf\Test;


use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;

class StatementImpUnitTest extends StatementAbstractTest {

    function newLiteralInstance($value, $lang = null, $datatype = null)
    {
        return new LiteralImpl($value, $lang, $datatype);
    }

    function newNamedNodeInstance($uri)
    {
        return new NamedNodeImpl($uri);
    }

    function newVariableInstance($value)
    {
        return new VariableImpl($value);
    }

    function newBlankNodeInstance($id)
    {
        return new BlankNodeImpl($id);
    }

    function newInstance($subject, $predicate, $object, $graph = null)
    {
        return new StatementImpl($subject, $predicate, $object);
    }
}