<?php
namespace Saft\Rdf;

abstract class NamedNode implements Node
{
    /**
     * @return boolean
     */
    public function isConcrete()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isLiteral()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isNamed()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isBlank()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isReturnable()
    {
        return false;
    }

    /**
     * @return boolean
     */
    abstract public function equals($tocompare);
    
    /**
     * @return \Saft\Rdf\Uri
     */
    abstract public function getUri();

    /**
     * @return string
     */
    public function toNT()
    {
        return '<' . $this->getUri() . '>';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUri();
    }
}
