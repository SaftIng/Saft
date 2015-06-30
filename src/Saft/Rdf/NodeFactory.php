<?php

namespace Saft\Rdf;

/**
 * The NodeFactory interface abstracts the creating of new instances of RDF nodes by hiding different implementation
 * details.
 */
interface NodeFactory
{
    /**
     * Create a new RDF Literal Node
     *
     * @param string $value The value of the literal
     * @param string|Node $datatype The datatype of the literal (NamedNode, optional)
     * @param string $lang The language tag of the literal (optional)
     * @return Literal
     */
    public function createLiteral($value, $datatype = null, $lang = null);

    /**
     * Create a new RDF Named Node
     *
     * @param string $uri The URI of the Named Node
     * @return NamedNode
     */
    public function createNamedNode($uri);

    /**
     * Create a new RDF Blank Node
     *
     * @param string $blankId The identifier for the blank node
     * @return BlankNode
     */
    public function createBlankNode($blankId);

    /**
     * Create a new pattern node, which matches any RDF Node
     *
     * @return Node
     */
    public function createAnyPattern();

    /**
     * Creates an RDF Node based on a N-Triples/N-Quads node string.
     *
     * @param string $string the N-Triples/N-Quads node string
     * @return Node
     * @throws \Exception if no node could be created e.g. because of a syntax error in the node string
     */
    public function createNodeFromNQuads($string);
}
