<?php
namespace Saft\Rdf\Literal\Boolean;

/**
 * Represents a boolean literal whose value can either be true or false.
 */
class Boolean extends \Saft\Rdf\Literal
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLiteralValue();
    }
    
    /**
     * Constructor. It sets $value but ignores $lang.
     * 
     * @param mixed $value
     * @param string $lang optional
     */
    public function __construct($value, $lang = null)
    {
        $this->value = $value;
    }
    
    /**
     * @param \Saft\Rdf\Node $toCompare
     * @return boolean
     */
    public function equals(\Saft\Rdf\Node $toCompare)
    {
        // Strict comparison: It only compares if given $toCompare is a boolean too,
        // otherwise it returns false.
        if(true === $toCompare->isLiteral()
           && $toCompare instanceOf \Saft\Rdf\Literal\Boolean) {
            return $this->value === $toCompare->getValue();
        }
        
        return false;
    }
    
    /**
     * @return string
     */
    public function getDatatype()
    {
        return 'http://www.w3.org/2001/XMLSchema#boolean';
    }
    
    /**
     * @return null
     */
    public function getLanguage()
    {
        return null;
    }
    
    /**
     * @return boolean
     */
    public function getValue()
    {
        return $this->value;
    }

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
