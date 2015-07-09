<?php

namespace Saft\Rdf;

/**
 * This interface is common for blank nodes according to RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-blank-nodes}
 *
 * @api
 * @package Saft\Rdf
 * @since 0.1
 */
interface BlankNode extends Node
{
    /**
     * Returns the blank ID of this blank node.
     *
     * @return string Blank ID.
     * @api
     * @since 0.1
     */
    public function getBlankId();
}
