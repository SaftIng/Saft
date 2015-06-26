<?php

namespace Saft\Rdf;

interface NodeFactory
{
    public function createLiteral($value, $lang = null, $datatype = null);

    public function createNamedNode($uri);

    public function createBlankNode($blankId);

    public function createAnyPattern();

    /**
     * Creates an RDF Node based on a N-Triples/N-Quads node string.
     *
     * @param $string string the N-Triples/N-Quads node string
     * @throws \Exception if no node could be created e.g. because of a syntax error in the node string
     */
    public function createNodeFromNQuads($string);
}
