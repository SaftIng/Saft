<?php

namespace Saft\Rdf;

class NodeUtils
{
    /**
     * @param NodeFactory $nodeFactory Instance of the NodeFactory to use.
     * @param string      $value       Value of the node.
     * @param string      $type        Can be uri, bnode, var or literal
     * @param string      $datatype    URI of the datatype (optional)
     * @param string      $language    Language tag (optional)
     * @return Node Node instance
     */
    public static function createNodeInstance(
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
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are
     * not checked. Instead, only characters disallowed an all URIs lead to a
     * rejection of the check.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     */
    public static function simpleCheckURI($string)
    {
        $regEx = '/^([a-zA-Z][a-zA-Z0-9+.-]+):([^\x00-\x0f\x20\x7f<>{}|\[\]`"^\\\\])+$/';
        return (1 === preg_match($regEx, (string)$string));
    }
}
