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
        $xsd = 'http://www.w3.org/2001/XMLSchema#';
        
        // If a language was set, than datatype is not possible.
        if (2 <= strlen($this->lang)) {
            return null;
        }
        
        /**
         * An overview about all XML Schema datatypes:
         * http://www.w3.org/TR/xmlschema-2/#built-in-datatypes
         */
        
        // xsd:???
        if (null === $this->value) {
            throw new \Exception('TODO: Implement case for getDatatype when value is null.');
        
        // xsd:boolean
        } elseif (true === is_bool($this->value)) {
            /**
             * Note that according to [1] the lexical representation of a boolean
             * is defined as:
             * 
             * > An instance of a datatype that is defined as boolean can have 
             * > the following legal literals {true, false, 1, 0}.
             * 
             * But because of PHP's dynamic type system and the fact, that an user
             * can change values of a variable when he wants, we only determine the
             * values true and false as boolean.
             * 
             * [1] - http://www.w3.org/TR/xmlschema-2/#boolean
             */
            return $xsd . 'boolean';
        
        // xsd:string
        } elseif (true === is_string($this->value)) {
            return $xsd . 'string';
        
        // xsd:integer
        } elseif (true === is_int($this->value)) {
            return $xsd . 'integer';
        
        // xsd:decimal
        } elseif (true === is_float($this->value)) {
            return $xsd . 'decimal';
        
        // In case it can't determine the type of the value.
        } else {
            throw new \Exception('Value has no valid XML schema datatype.');
        }
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
