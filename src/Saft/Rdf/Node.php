<?php

namespace Saft\Rdf;

interface Node
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * Check if a given instance of \Saft\Rdf\Node is equal to this instance.
     *
     * @param \Saft\Rdf\Node $toCompare
     * @return boolean True, if both instances are semantically equal, false otherwise.
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
     * TODO: what for is this?
     * @return boolean
     */
    public function isReturnable();

    /**
     * @return boolean
     */
    public function isVariable();

    /**
     * @return string
     */
    public function toNQuads();
}
