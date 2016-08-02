<?php

namespace Saft\Rdf;

/**
 * This interface is common for named nodes according to RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-IRIs}
 *
 * @api
 * @package Saft\Rdf
 * @api
 * @since 0.1
 */
interface NamedNode extends Node
{
    /**
     * Returns the URI of the node.
     *
     * @return string URI of the node.
     * @api
     * @since 0.1
     */
    public function getUri();
}
