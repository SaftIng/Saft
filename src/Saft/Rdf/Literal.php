<?php

namespace Saft\Rdf;

/**
 * Interface Literal
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
