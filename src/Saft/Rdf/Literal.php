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
     * @return mixed
     */
    public function getValue();

    /**
     * @return string
     * @throws \Exception
     */
    public function getDatatype();

    /**
     * @return string|null
     */
    public function getLanguage();
}
