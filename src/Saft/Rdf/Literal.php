<?php
namespace Saft\Rdf;

abstract class Literal implements Node
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLiteralValue();
    }
    
    /**
     * @return boolean
     */
    abstract public function equals($toCompare);
    
    /**
     * @return string
     */
    abstract public function getDatatype();
    
    /**
     * @return string
     */
    abstract public function getLanguage();
    
    /**
     * @return mixed
     */
    abstract public function getLiteralValue();

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
        return true;
    }

    /**
     * @return boolean
     */
    public function isNamed()
    {
        return false;
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
     * @return string
     */
    public function toNT()
    {
        $string = '"' . $this->geLiteralValue() . '"';
        if ($this->getLanguage() !== null) {
            $string .= '@' . $this->getLanguage();
        } else if ($this->getDatatype() !== null) {
            $string .= '^^<' . $this->getDatatype() . '>';
        }

        return $string;
    }
}
