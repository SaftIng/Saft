<?php

namespace Saft\Rdf;

class NodeFactoryImpl implements NodeFactory
{
    /**
     * @var NodeUtils
     */
    protected $rdfHelpers;

    /**
     *
     */
    public function __construct(RdfHelpers $rdfHelpers)
    {
        $this->nodeUtils = $rdfHelpers;
    }

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
        return new LiteralImpl($this->nodeUtils, $value, $datatype, $lang);
    }

    public function createNamedNode($uri)
    {
        return new NamedNodeImpl($this->nodeUtils, $uri);
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
        $regex = '/' . $this->nodeUtils->getRegexStringForNodeRecognition(
            true, true, true, true, true, true
        ) .'/si';

        $string = trim($string);

        preg_match($regex, $string, $matches);

        if (0 == count($matches)) {
            throw new \Exception('Invalid parameter $string given. Our regex '. $regex .' doesnt apply.');
        }

        $firstChar = substr($matches[0], 0, 1);

        // http://...
        if ('<' == $firstChar) {
            return $this->createNamedNode(str_replace(array('<', '>'), '', $matches[1]));
        // ".."^^<
        } elseif (false !== strpos($matches[0], '"^^<')) {
            return $this->createLiteral($matches[9], $matches[10]);
        // "foo"@en
        } elseif (false !== strpos($matches[0], '"@')) {
            return $this->createLiteral($matches[12], null, $matches[13]);
        // "foo"
        } elseif ('"' == $firstChar) {
            return $this->createLiteral($matches[15]);
        // _:foo
        } elseif ($this->nodeUtils->simpleCheckBlankNodeId($matches[0])) {
            return $this->createBlankNode($matches[4]);
        // 0-9 (simple number, multi digits)
        } elseif (0 < (int)$matches[0]) {
            return $this->createLiteral(
                $matches[16],
                $this->createNamedNode('http://www.w3.org/2001/XMLSchema#double')
            );
        } else {
            throw new \Exception('Unknown case for: '. $matches[1]);
        }
        throw new \Exception("The given string (\"$string\") is not valid or doesn't represent any RDF node");
    }

    /**
     * Helper function, which is useful, if you have all the meta information about a Node and want to create
     * the according Node instance.
     *
     * @param string      $value       Value of the node.
     * @param string      $type        Can be uri, bnode, var or literal
     * @param string      $datatype    URI of the datatype (optional)
     * @param string      $language    Language tag (optional)
     * @return Node Node instance, which type is one of: NamedNode, BlankNode, Literal, AnyPattern
     * @throws \Exception if an unknown type was given.
     * @throws \Exception if something went wrong during Node creation.
     * @api
     * @since 0.8
     */
    public function createNodeInstanceFromNodeParameter($value, $type, $datatype = null, $language = null)
    {
        switch ($type) {
            case 'uri':
                return $this->createNamedNode($value);

            case 'bnode':
                return $this->createBlankNode($value);

            case 'literal':
                return $this->createLiteral($value, $datatype, $language);

            case 'typed-literal':
                return $this->createLiteral($value, $datatype, $language);

            case 'var':
                return $this->createAnyPattern();

            default:
                throw new \Exception('Unknown $type given: '. $type);
        }
    }
}
