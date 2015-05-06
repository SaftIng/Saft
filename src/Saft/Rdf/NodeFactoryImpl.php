<?php

namespace Saft\Rdf;

class NodeFactoryImpl implements NodeFactory
{
    public function createLiteral($value, $datatype = null, $lang = null)
    {
        return new LiteralImpl($value, $datatype, $lang);
    }

    public function createNamedNode($uri)
    {
        return new NamedNodeImpl($uri);
    }

    public function createBlankNode($blankId)
    {
        return new BlankNodeImpl($blankId);
    }

    public function createAnyPattern()
    {
        return new AnyPatternImpl();
    }
}
