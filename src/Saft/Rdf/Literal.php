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
     * Forked from Erfurt_Utils.php of the Erfurt project.
     * 
     * Build a Turtle-compatible literal string out of an RDF/PHP array object.
     * This string is used as the canonical representation for object values in Erfurt.
     *
     * @see {http://www.w3.org/TeamSubmission/turtle/}
     * @param string $value             Value of the later triple
     * @param string $datatype optional Data type of the $value (XML-Datatype URL)
     * @param string $lang     optional Language of the $value
     * @return string
     */
    public static function buildLiteralString($value, $datatype = null, $lang = null)
    {
        $longString = false;
        $quoteChar  = (strpos($value, '"') !== false) ? "'" : '"';
        $value      = (string)$value;
        
        // datatype-specific treatment
        switch ($datatype) {
            case "http://www.w3.org/2001/XMLSchema#boolean":
                // it seems that either Virtuoso or ODBC convert a xmls:boolean
                // value to an integer later on. So we will cast it internally
                // to an string, to keep the value, but, unfortunately, we lost
                // the datatype too. 
                $search  = array("0", "1");
                $replace = array("false", "true");
                $value   = str_replace($search, $replace, $value);
                
                $datatype = "http://www.w3.org/2001/XMLSchema#string";
                break;
                
            /* no normalization needed for these types */    
            case "http://www.w3.org/2001/XMLSchema#decimal":    break;
            case "http://www.w3.org/2001/XMLSchema#integer":    break;
            case "http://www.w3.org/2001/XMLSchema#int":        break;
            case "http://www.w3.org/2001/XMLSchema#float":      break;
            case "http://www.w3.org/2001/XMLSchema#double":     break;
            case "http://www.w3.org/2001/XMLSchema#duration":   break;
            case "http://www.w3.org/2001/XMLSchema#dateTime":   break;
            case "http://www.w3.org/2001/XMLSchema#date":       break;
            case "http://www.w3.org/2001/XMLSchema#gMonthDay":  break;
            case "http://www.w3.org/2001/XMLSchema#anyURI":     break;
            case "http://www.w3.org/2001/XMLSchema#time":       break;
            /* no normalization needed for these types */
            case "":    /* fallthrough */
            case null:  /* fallthrough */
            case "http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral":   /* fallthrough */
            case "http://www.w3.org/2001/XMLSchema#string":
            default:
                $value = addcslashes($value, $quoteChar);
                
                /** 
                 * TODO Check for characters not allowed in a short literal
                 * {@link http://www.w3.org/TR/rdf-sparql-query/#rECHAR}
                 */
                if ($pos = preg_match('/[\x5c\r\n"]/', $value)) {
                    $longString = true;
                }
                break;
        }
        
        // add short, long literal quotes respectively
        $value = $quoteChar . ($longString ? ($quoteChar . $quoteChar) : '')
               . $value 
               . $quoteChar . ($longString ? ($quoteChar . $quoteChar) : '');
        
        // add datatype URI/lang tag
        if (!empty($datatype)) {
            $value .= '^^<' . (string)$datatype . '>';
        } else if (!empty($lang)) {
            $value .= '@' . (string)$lang;
        }
        
        return $value;
    }
    
    /**
     * @see \Saft\Node
     */
    public function equals(\Saft\Rdf\Node $toCompare)
    {
        // Only compare, if given instance is a literal
        if (true == $toCompare->isLiteral()) {
            return $this->getValue() === $toCompare->getValue();
        }
        
        // TODO what about cases like 1 == 1.0 or 1 == "1"?
        
        return false;
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
