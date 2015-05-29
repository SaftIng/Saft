<?php

namespace Saft\Rdf;

interface NodeFactory
{
    public function createLiteral($value, $lang = null, $datatype = null);

    public function createNamedNode($uri);

    public function createBlankNode($blankId);

    public function createAnyPattern();

    public function createNodeFromNQuads($string);
}
