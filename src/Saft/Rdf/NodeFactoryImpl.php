<?php

namespace Saft\Rdf;

class NodeFactoryImpl implements NodeFactory
{
    /**
     * @param string $value
     * @param Node|string $datatype (optional)
     * @param string $lang (optional)
     */
    public function createLiteral($value, $datatype = null, $lang = null)
    {
        if ($datatype !== null) {
            if (!$datatype instanceof Node) {
                $datatype = $this->createNamedNode($datatype);
            } elseif (!$datatype->isNamed()) {
                throw new \Exception("Argument datatype has to be a named node.");
            }
        }
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
