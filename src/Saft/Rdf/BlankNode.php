<?php

namespace Saft\Rdf;

class BlankNode implements Node
{

    /**
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    /**
     * @param mixed $value
     * @param string $lang optional
     */
    public function __construct($value, $lang = null)
    {
        // TODO: Implement __construct() method.
    }

    /**
     * Check if a given instance of \Saft\Rdf\Node is equal to this instance.
     *
     * @param \Saft\Rdf\Node $toCompare
     * @return boolean True, if both instances are semantically equal, false otherwise.
     */
    public function equals(\Saft\Rdf\Node $toCompare)
    {
        // TODO: Implement equals() method.
    }

    /**
     * @return boolean
     */
    public function isConcrete()
    {
        // TODO: Implement isConcrete() method.
    }

    /**
     * @return boolean
     */
    public function isLiteral()
    {
        // TODO: Implement isLiteral() method.
    }

    /**
     * @return boolean
     */
    public function isNamed()
    {
        // TODO: Implement isNamed() method.
    }

    /**
     * @return boolean
     */
    public function isBlank()
    {
        // TODO: Implement isBlank() method.
    }

    /**
     * @return boolean
     */
    public function isReturnable()
    {
        // TODO: Implement isReturnable() method.
    }
    
    /**
     * @return boolean
     */
    public function isVariable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function toNT()
    {
        // TODO: Implement toNT() method.
    }
}
