<?php

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\Node;
use Saft\Rdf\NodeFactoryImpl as SaftNodeFactoryImpl;
use Saft\Rdf\NodeUtils;

class NodeFactory extends SaftNodeFactoryImpl
{
    /**
     * @var NodeUtils
     */
    protected $nodeUtils;

    /**
     * @var string
     */
    protected static $xsdString = 'http://www.w3.org/2001/XMLSchema#string';

    /**
     * @var string
     */
    protected static $rdfLangString = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString';

    /**
     * @param NodeUtils $nodeUtils
     */
    public function __construct(NodeUtils $nodeUtils)
    {
        $this->nodeUtils = $nodeUtils;
    }

    /**
     * @param  string $value
     * @param  Node|string $datatype optional
     * @param  string $lang     optional
     * @return Literal
     */
    public function createLiteral($value, $datatype = null, $lang = null)
    {
        if ($value === null) {
            throw new \Exception('Can\'t initialize literal with null as value.');
        } elseif (!is_string($value)) {
            throw new \Exception("The literal value has to be of type string");
        }

        $datatypeUri = null;

        $world = librdf_php_get_world();

        if ($datatype !== null) {
            if (!$datatype instanceof Node) {
                // Ensure valid Node creation
                $datatype = $this->createNamedNode($datatype);
            } elseif (!$datatype->isNamed()) {
                throw new \Exception("Argument datatype has to be a named node.");
            }

            if ($lang !== null && $datatype->getUri() !== self::$rdfLangString) {
                throw new \Exception('Language tagged Literals must have <' . self::$rdfLangString . '> datatype.');
            }

            /*
             * Make sure that the no language is set, since redland doesn't allow a language tag to be set if a
             * datatype is given.
             */
            if ($lang === null) {
                // TODO catch invalid URIs
                $datatypeUri = librdf_new_uri($world, $datatype->getUri());
            }
        }

        /*
         * This redland method does only support either $lang or $datatypeUri or both null
         */
        $redlandNode = librdf_new_node_from_typed_literal($world, $value, $lang, $datatypeUri);

        if ($redlandNode === null) {
            throw new \Exception('Initialization of redland node failed.');
        }

        return new Literal($redlandNode, $this, $this->nodeUtils);
    }

    /**
     * @param  string $uri URI of the new named node.
     * @return NamedNode
     */
    public function createNamedNode($uri)
    {
        if ($uri === null) {
            throw new \Exception('Can\'t initialize node with null.');
        }

        if (is_string($uri) && !$this->nodeUtils->simpleCheckURI($uri)) {
            throw new \Exception('Invalid URI was given for RDF NamedNode creation.');
        }

        if (!is_string($uri)) {
            throw new \Exception('Parameter $uri is not a string and therefore not valid.');
        }

        // TODO catch invalid URIs
        $world = librdf_php_get_world();
        $uri = librdf_new_uri($world, $uri);
        $redlandNode = librdf_new_node_from_uri($world, $uri);

        if ($redlandNode === null) {
            throw new \Exception('Initialization of redland node failed.');
        }

        return new NamedNode($redlandNode);
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
        $node = parent::createNodeInstanceFromNodeParameter($value, $type, $datatype, $language);

        if ($node->isNamed()) {
            return $this->createNamedNode($node->getUri());
        } elseif ($node->isLiteral()) {
            return $this->createLiteral($value, $datatype, $language);
        } elseif ($node->isBlank()) {
            return $this->createBlankNode($node->getBlankId());
        } else {
            throw new \Exception(
                'Invalid parameter given: '. $value .', '. $type .', '. $datatype .', '. $language
            );
        }
    }

    /**
     * @param Node $node
     * @return Node
     */
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
        throw new \Exception('This node type (' . get_class($node) . ') is not supported by Redland backend');
    }
}
