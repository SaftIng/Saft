<?php

namespace Saft\Rdf;

class NodeFactoryImpl implements NodeFactory
{
    const NAMED_NODE_REGEX = '/^<([^<>]+)>$/';
    const BLANK_NODE_REGEX = '/^_:(.+)$/';
    const LITERAL_DATATYPE_REGEX = '/^"(.+)"\^\^<([^<>]+)>$/';
    const LITERAL_LANG_REGEX = '/^"(.+)"@([\w\-]+)$/';
    const LITERAL_REGEX = '/^"(.*)"$/';

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

    /**
     * Creates an RDF Node based on a N-Triples/N-Quads node string.
     *
     * @param $string string the N-Triples/N-Quads node string
     * @throws \Exception if no node could be created e.g. because of a syntax error in the node string
     */
    public function createNodeFromNQuads($string)
    {
        if (preg_match(self::NAMED_NODE_REGEX, $string, $matches)) {
            return $this->createNamedNode($matches[1]);
        } elseif (preg_match(self::BLANK_NODE_REGEX, $string, $matches)) {
            return $this->createBlankNode($matches[1]);
        } elseif (preg_match(self::LITERAL_DATATYPE_REGEX, $string, $matches)) {
            return $this->createLiteral($matches[1], $matches[2]);
        } elseif (preg_match(self::LITERAL_LANG_REGEX, $string, $matches)) {
            return $this->createLiteral($matches[1], null, $matches[2]);
        } elseif (preg_match(self::LITERAL_REGEX, $string, $matches)) {
            return $this->createLiteral($matches[1]);
        }
        throw new \Exception("The given string (\"$string\") is not valid or doesn't represent any RDF node");
    }
}
