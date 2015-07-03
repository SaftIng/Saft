<?php

namespace Saft\Rdf;

/**
 * This interface is common for named nodes according to RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-IRIs}
 *
 * @api
 * @package Saft\Rdf
 */
interface NamedNode extends Node
{
    /**
     * Returns the URI of the node.
     *
     * @return string URI of the node.
     */
    public function getUri();
}
