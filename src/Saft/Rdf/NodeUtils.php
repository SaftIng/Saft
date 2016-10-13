<?php

namespace Saft\Rdf;

use Saft\Data\ParserSerializerUtils;

/**
 * Helper class which provides useful methods for Node related operations, for instance node creation or URI
 * checks.
 *
 * @api
 * @package Saft\Rdf
 * @since 0.1
 */
class NodeUtils
{
    protected $nodeFactory;
    protected $parserSerializerUtils;

    /**
     * @param NodeFactory $nodeFactory
     */
    public function __construct(NodeFactory $nodeFactory, ParserSerializerUtils $parserSerializerUtils)
    {
        $this->nodeFactory = $nodeFactory;
        $this->parserSerializerUtils = $parserSerializerUtils;
    }

    /**
     * Helper function, which is useful, if you have all the meta information about a Node and want to create
     * the according Node instance. It utilizes a NodeFactory instance to create Node instances.
     *
     * @param string      $value       Value of the node.
     * @param string      $type        Can be uri, bnode, var or literal
     * @param string      $datatype    URI of the datatype (optional)
     * @param string      $language    Language tag (optional)
     * @return Node Node instance, which type is one of: NamedNode, BlankNode, Literal, AnyPattern
     * @throws \Exception if an unknown type was given.
     * @api
     * @since 0.1
     */
    public function createNodeInstance($value, $type, $datatype = null, $language = null)
    {
        switch ($type) {
            case 'uri':
                return $this->nodeFactory->createNamedNode($value);

            case 'bnode':
                return $this->nodeFactory->createBlankNode($value);

            case 'literal':
                return $this->nodeFactory->createLiteral($value, $datatype, $language);

            case 'typed-literal':
                return $this->nodeFactory->createLiteral($value, $datatype, $language);

            case 'var':
                return $this->nodeFactory->createAnyPattern();

            default:
                throw new \Exception('Unknown $type given: '. $type);
        }
    }

    /**
     * Checks if a given string is a blank node ID. Blank nodes are usually structured like
     * _:foo, whereas _: comes first always.
     *
     * @param string $string String to check if its a blank node ID or not.
     * @return boolean True if given string is a valid blank node ID, false otherwise.
     */
    public function simpleCheckBlankNodeId($string)
    {
        return '_:' == substr($string, 0, 2);
    }

    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are not checked. Instead, only
     * characters disallowed an all URIs lead to a rejection of the check. Use this function, if you need a
     * basic check and if performance is an issuse. In case you need a more precise check, that function is
     * not recommended.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     * @api
     * @since 0.1
     */
    public function simpleCheckURI($string)
    {
        $regEx = '/^([a-zA-Z][a-zA-Z0-9+.-]+):([^\x00-\x0f\x20\x7f<>{}|\[\]`"^\\\\])+$/';
        return (1 === preg_match($regEx, (string)$string));
    }

    /**
     * @param string $s
     * @return string encoded string for n-quads
     */
    public function encodeStringLitralForNQuads($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace("\t", '\t', $s);
        $s = str_replace("\n", '\n', $s);
        $s = str_replace("\r", '\r', $s);
        $s = str_replace('"', '\"', $s);

        return $s;
    }
}
