<?php

namespace Saft\Rdf;

/**
 * This interface is common for Literals according to RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal}
 *
 * @package Saft\Rdf
 */
interface Literal extends Node
{
    /**
     * Get the value of the Literal in its string representations
     * @return string the value of the Literal
     */
    public function getValue();

    /**
     * Get the datatype URI of the Literal (this is always set according to the
     * standard).
     * @return Node the datatype of the Literal
     */
    public function getDatatype();

    /**
     * Get the language tag of this Literal or null of the Literal has no
     * language tag.
     * @return string|null the language tag or null if none is given
     */
    public function getLanguage();
}
