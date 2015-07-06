<?php

namespace Saft\Rdf;

/**
 * Helper class which provides useful methods for Node related operations, for instance node creation or URI
 * checks.
 *
 * @api
 * @package Saft\Rdf
 */
class NodeUtils
{
    /**
     * Helper function which is useful, if you have all the meta information about a Node and want to create
     * the according Node instance. It utilizes a NodeFactory instance to create Node instances.
     *
     * @param NodeFactory $nodeFactory Instance of the NodeFactory to use.
     * @param string      $value       Value of the node.
     * @param string      $type        Can be uri, bnode, var or literal
     * @param string      $datatype    URI of the datatype (optional)
     * @param string      $language    Language tag (optional)
     * @return Node Node instance, which type is one of: NamedNode, BlankNode, Literal, AnyPattern
     * @throws \Exception if an unknown type was given.
     */
    public function createNodeInstance(
        NodeFactory $nodeFactory,
        $value,
        $type,
        $datatype = null,
        $language = null
    ) {
        switch ($type) {
            case 'uri':
                return $nodeFactory->createNamedNode($value);

            case 'bnode':
                return $nodeFactory->createBlankNode($value);

            case 'literal':
                return $nodeFactory->createLiteral($value, $datatype, $language);

            case 'var':
                return $nodeFactory->createAnyPattern();

            default:
                throw new \Exception('Unknown $type given: '. $type);
        }
    }

    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are not checked. Instead, only
     * characters disallowed an all URIs lead to a rejection of the check. Use this function, if you need a
     * basic check and if performance is an issuse. In case you need a more precise check, that function is
     * not recommended.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     */
    public function simpleCheckURI($string)
    {
        $regEx = '/^([a-zA-Z][a-zA-Z0-9+.-]+):([^\x00-\x0f\x20\x7f<>{}|\[\]`"^\\\\])+$/';
        return (1 === preg_match($regEx, (string)$string));
    }
}
