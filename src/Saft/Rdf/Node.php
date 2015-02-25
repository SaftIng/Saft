<?php

namespace Saft\Rdf;

interface Node
{
    /**
     * @return string
     */
    public function __toString();
    
    /**
     * @param mixed $value
     * @param string $lang optional
     * @param string $datatype optional
     */
    public function __construct($value, $lang = null);
    
    /**
     * @param \Saft\Rdf\Node $toCompare
     * @return boolean
     */
    public function equals(\Saft\Rdf\Node $toCompare);
    
    /**
     * @return boolean
     */
    public function isConcrete();

    /**
     * @return boolean
     */
    public function isLiteral();

    /**
     * @return boolean
     */
    public function isNamed();

    /**
     * @return boolean
     */
    public function isBlank();

    /**
     * @return boolean
     */
    public function isReturnable();

    /**
     * @return string
     */
    public function toNT();
}
