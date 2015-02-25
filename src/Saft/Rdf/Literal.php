<?php

namespace Saft\Rdf;

class Literal implements Node
{
    /**
     * @var mixed
     */
    protected $value;
    
    /**
     * @var string
     */
    protected $lang;
    
    /**
     * @param mixed $value
     * @param string $lang optional
     */
    public function __construct($value, $lang = null)
    {
        $this->value = $value;
        $this->lang = $lang;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }
    
    /**
     * @param \Saft\Rdf\Literal $toCompare
     * @return boolean
     */
    public function equals(\Saft\Rdf\Node $toCompare)
    {
        
    }
    
    /**
     * @return string
     */
    public function getDatatype()
    {
        
    }
    
    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->lang;
    }
    
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
    public function isReturnable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function toNT()
    {
        $string = '"' . $this->getValue() . '"';
        
        if ($this->getLanguage() !== null) {
            $string .= '@' . $this->getLanguage();
        } elseif ($this->getDatatype() !== null) {
            $string .= '^^<' . $this->getDatatype() . '>';
        }

        return $string;
    }
}
