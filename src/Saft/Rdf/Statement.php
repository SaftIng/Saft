<?php

namespace Saft\Rdf;

interface Statement
{
    /**
     * @return NamedNode|BlankNode
     */
    public function getSubject();

    /**
     * @return NamedNode
     */
    public function getPredicate();

    /**
     * @return Node
     */
    public function getObject();

    /**
     * @return NamedNode|null
     */
    public function getGraph();

    /**
     * @return boolean
     */
    public function isQuad();
    
    /**
     * @return boolean
     */
    public function isTriple();
}
