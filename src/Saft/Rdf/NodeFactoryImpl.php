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

class NodeFactoryImpl implements NodeFactory
{
    /**
     * @param CommonNamespaces $commonNamespaces if null, CommonNamespaces will used automatically
     */
    public function __construct(CommonNamespaces $commonNamespaces = null)
    {
        if (null == $commonNamespaces) {
            $this->commonNamespaces = new CommonNamespaces();
        } else {
            $this->commonNamespaces = $commonNamespaces;
        }
    }

    /**
     * @param string      $value
     * @param Node|string $datatype (optional)
     * @param string      $lang     (optional)
     *
     * @return Literal
     */
    public function createLiteral($value, $datatype = null, $lang = null)
    {
        if ($datatype !== null) {
            if (!$datatype instanceof Node) {
                $datatype = $this->createNamedNode($datatype);
            } elseif (!$datatype->isNamed()) {
                throw new \Exception('Argument datatype has to be a named node.');
            }
        }

        return new LiteralImpl($value, $datatype, $lang);
    }

    /*
     * @return NamedNode
     */
    public function createNamedNode($uri)
    {
        return new NamedNodeImpl($this->commonNamespaces->extendUri($uri));
    }

    /*
     * @return BlankNode
     */
    public function createBlankNode($blankId)
    {
        return new BlankNodeImpl($blankId);
    }

    /*
     * @return AnyPattern
     */
    public function createAnyPattern()
    {
        return new AnyPatternImpl();
    }

    /**
     * Creates an RDF Node based on a N-Triples/N-Quads node string.
     *
     * @param $string string the N-Triples/N-Quads node string
     *
     * @return Node
     *
     * @throws \Exception if no node could be created e.g. because of a syntax error in the node string
     */
    public function createNodeFromNQuads($string)
    {
        $regex = '/'.$this->getRegexStringForNodeRecognition(
            true, true, true, true, true, true
        ).'/si';

        $string = trim($string);

        preg_match($regex, $string, $matches);

        if (0 == count($matches)) {
            throw new \Exception('Invalid parameter $string given. Our regex '.$regex.' doesnt apply.');
        }

        $firstChar = substr($matches[0], 0, 1);

        // http://...
        if ('<' == $firstChar) {
            return $this->createNamedNode(str_replace(['<', '>'], '', $matches[1]));
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
        } elseif ($this->simpleCheckBlankNodeId($matches[0])) {
            return $this->createBlankNode($matches[4]);
        // 0-9 (simple number, multi digits)
        } elseif (0 < (int) $matches[0]) {
            return $this->createLiteral(
                $matches[16],
                $this->createNamedNode('http://www.w3.org/2001/XMLSchema#double')
            );
        } else {
            throw new \Exception('Unknown case for: '.$matches[1]);
        }
        throw new \Exception("The given string (\"$string\") is not valid or doesn't represent any RDF node");
    }

    /**
     * Helper function, which is useful, if you have all the meta information about a Node and want to create
     * the according Node instance.
     *
     * @param string $value    value of the node
     * @param string $type     Can be uri, bnode, var or literal
     * @param string $datatype URI of the datatype (optional)
     * @param string $language Language tag (optional)
     *
     * @return Node Node instance, which type is one of: NamedNode, BlankNode, Literal, AnyPattern
     *
     * @throws \Exception if an unknown type was given
     * @throws \Exception if something went wrong during Node creation
     *
     * @api
     *
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
                throw new \Exception('Unknown $type given: '.$type);
        }
    }

    /**
     * Returns the regex string to get a node from a triple/quad.
     *
     * @param bool $useVariables     optional, default is false
     * @param bool $useNamespacedUri optional, default is false
     *
     * @return string
     */
    protected function getRegexStringForNodeRecognition(
        $useBlankNode = false,
        $useNamespacedUri = false,
        $useTypedString = false,
        $useLanguagedString = false,
        $useSimpleString = false,
        $useSimpleNumber = false,
        $useVariables = false
    ) {
        $regex = '(<([a-z]{2,}:[^\s]*)>)'; // e.g. <http://foobar/a>

        if (true == $useBlankNode) {
            $regex .= '|(_:([a-z0-9A-Z_]+))'; // e.g. _:foobar
        }

        if (true == $useNamespacedUri) {
            $regex .= '|(([a-z0-9]+)\:([a-z0-9]+))'; // e.g. rdfs:label
        }

        if (true == $useTypedString) {
            // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            $regex .= '|(\"(.*?)\"\^\^\<([^\s]+)\>)';
        }

        if (true == $useLanguagedString) {
            $regex .= '|(\"(.*?)\"\@([a-z\-]{2,}))'; // e.g. "Foo"@en
        }

        if (true == $useSimpleString) {
            $regex .= '|(\"(.*?)\")'; // e.g. "Foo"
        }

        if (true == $useSimpleNumber) {
            $regex .= '|([0-9]{1,})'; // e.g. 42
        }

        if (true == $useVariables) {
            $regex .= '|(\?[a-z0-9\_]+)'; // e.g. ?s
        }

        return $regex;
    }

    /**
     * Checks if a given string is a blank node ID. Blank nodes are usually structured like
     * _:foo, whereas _: comes first always.
     *
     * @param string $string string to check if its a blank node ID or not
     *
     * @return bool true if given string is a valid blank node ID, false otherwise
     */
    protected function simpleCheckBlankNodeId($string)
    {
        return '_:' == substr($string, 0, 2);
    }
}
