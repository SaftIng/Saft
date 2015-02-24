<?php
namespace Saft\Rdf;

interface Node
{
    /**
     * @return string
     */
    public function __toString();
    
    /**
     * @return boolean
     */
    public function equals($tocompare);
    
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
