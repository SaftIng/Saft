<?php

namespace Saft\Rdf;

/**
 * The NodeFactory interface abstracts the creating of new instances of RDF nodes by hiding different implementation
 * details.
 *
 * @api
 * @package Saft\Rdf
 */
interface NodeFactory
{
    /**
     * Create a new RDF literal node instance. Details how to create such an instance may differ between different
     * implementations of the NodeFactory.
     *
     * @param string $value The value of the literal
     * @param string|Node $datatype The datatype of the literal (NamedNode, optional)
     * @param string $lang The language tag of the literal (optional)
     * @return Literal Instance of Literal.
     */
    public function createLiteral($value, $datatype = null, $lang = null);

    /**
     * Create a new RDF named node. Details how to create such an instance may differ between different
     * implementations of the NodeFactory.
     *
     * @param string $uri The URI of the named node
     * @return NamedNode Instance of NamedNode.
     */
    public function createNamedNode($uri);

    /**
     * Create a new RDF blank node. Details how to create such an instance may differ between different
     * implementations of the NodeFactory.
     *
     * @param string $blankId The identifier for the blank node
     * @return BlankNode Instance of BlankNode.
     */
    public function createBlankNode($blankId);

    /**
     * Create a new pattern node, which matches any RDF Node instance.
     *
     * @return Node Instance of Node, which acts like an AnyPattern.
     */
    public function createAnyPattern();

    /**
     * Creates an RDF Node based on a n-triples/n-quads node string.
     *
     * @param string $string N-triples/n-quads node string to use.
     * @return Node Node instance, which type must be one of the following: NamedNode, BlankNode, Literal
     * @throws \Exception if no node could be created e.g. because of a syntax error in the node string
     */
    public function createNodeFromNQuads($string);
}
