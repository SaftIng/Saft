<?php

namespace Saft\Rdf;

class NamedNode implements \Saft\Rdf\Node
{
    /**
     * @var string
     */
    protected $value;
    
    /**
     * @param mixed $value The URI of the node.
     * @param string $lang optional Will be ignore because an NamedNode has no language.
     */
    public function __construct($value, $lang = null)
    {
        if(true === \Saft\Rdf\NamedNode::check($value)) {
            $this->value = $value;
        } else {
            throw new \Exception('Paramter $value is not a valid URI.');
        }
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }
    
    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are 
     * not checked. Instead, only characters disallowed an all URIs lead to a 
     * rejection of the check.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     */
    public static function check($string)
    {
        $regEx = '/^([a-zA-Z][a-zA-Z0-9+.-]+):([^\x00-\x0f\x20\x7f<>{}|\[\]`"^\\\\])+$/';        
        return (1 === preg_match($regEx, (string)$string));
    }
    
    /**
     * @see \Saft\Rdf\Node
     */
    public function equals(\Saft\Rdf\Node $toCompare)
    {
        // It only compares URIs, everything will be quit with false.
        if (true === $toCompare->isNamed()) {
            return $this->getValue() == $toCompare->getValue();
        }
        
        return false;
    }
    
    /**
     * @return string URI of the node.
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
        return false;
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
     * @return string
     */
    public function toNT()
    {
        return '<' . $this->uri . '>';
    }
}
