<?php
namespace Saft\Backend\Redland\Rdf;

use \Saft\Rdf\Node;
use \Saft\Rdf\NodeFactory as SaftNodeFactory;

class NodeFactory implements SaftNodeFactory
{
    protected static $xsdString = "http://www.w3.org/2001/XMLSchema#string";
    protected static $rdfLangString = "http://www.w3.org/1999/02/22-rdf-syntax-ns#langString";

    public function createLiteral($value, $datatype = null, $lang = null)
    {
        if ($value === null) {
            throw new \Exception("Can't initialize literal with null as value.");
        }

        if ($lang !== null && $datatype !== null && $datatype !== self::$rdfLangString) {
            throw new \Exception("Language tagged Literals must have <" . self::$rdfLangString . "> datatype.");
        }

        $world = librdf_new_world();

        /*
         * Make sure that the no language is set, since redland doesn't allow a language tag to be set if a datatype is
         * given
         */
        if ($datatype !== null && $lang === null) {
            // TODO catch invalid URIs
            $datatypeUri = librdf_new_uri($world, $datatype);
        } else {
            $datatypeUri = null;
        }

        /*
         * This redland method does only support either $lang or $datatypeUri or both null
         */
        $redlandNode = librdf_new_node_from_typed_literal($world, $value, $lang, $datatypeUri);

        if ($redlandNode === null) {
            throw new \Exception("Initialization of redland node failed");
        }

        return new Literal($redlandNode);
    }

    public function createNamedNode($uri)
    {
        if ($uri === null) {
            throw new \Exception("Can't initialize node with null.");
        }

        // TODO catch invalid URIs
        $world = librdf_new_world();
        $uri = librdf_new_uri($world, $uri);
        $redlandNode = librdf_new_node_from_uri($world, $uri);

        if ($redlandNode === null) {
            throw new \Exception("Initialization of redland node failed");
        }

        return new NamedNode($redlandNode);
    }

    public function createBlankNode($blankId)
    {

    }

    public function createAnyPattern()
    {

    }

    public function createRedlandNodeFromNode(Node $node)
    {
        if ($node instanceof NamedNode || $node instanceof Literal || $node instanceof BlankNode) {
            return $node->getRedlandNode();
        } elseif ($node->isNamed()) {
            return $this->createNamedNode($node->getUri())->getRedlandNode();
        } elseif ($node->isLiteral()) {
            return $this->createLiteral(
                $node->getValue(),
                $node->getDatatype(),
                $node->getLanguage()
            )->getRedlandNode();
        } elseif ($node->isBlank()) {
            return $this->createBlankNode($node->getBlankId())->getRedlandNode();
        }
        throw new \Exception("This node type (" . get_class($node) . ") is not supported by Redland backend");
    }
}
