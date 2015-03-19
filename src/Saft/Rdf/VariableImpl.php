<?php

namespace Saft\Rdf;

class VariableImpl implements Variable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param mixed $name optional The Name of the variable. If not given, a random hash will be used later on.
     * @param string $lang optional Will be ignore because a Variable has no language.
     * @throws \Exception If parameter $name is not a valid URI.
     */
    public function __construct($name = null, $lang = null)
    {
        // $name is a variable, like ?s
        if (null !== $name && true === is_string($name) && '?' == substr($name, 0, 1)) {
            $this->name = $name;
            
        // $name is null, means we have to generate a random variable name
        } elseif (null === $name) {
            $variable = hash('sha1', microtime(true) . rand(0, time()));
            $this->name = '?'. substr($variable, 0, 10);
        } else {
            throw new \Exception('Parameter $name is not a string or not null.');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Checks if a given string is a variable (?s).
     *
     * @param string $string String to check if its a variable or not.
     * @return boolean
     */
    public static function check($string)
    {
        $matches = array();
        preg_match_all('/\?[a-zA-Z0-9\_]+/', $string, $matches);

        if (true === isset($matches[0][0])
            && 1 == count($matches[0][0])
            && strlen($matches[0][0]) == strlen($string)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a given instance of \Saft\Rdf\Node is equal to this instance.
     *
     * @param \Saft\Rdf\Node $toCompare
     * @return boolean True, if both instances are semantically equal, false otherwise.
     */
    public function equals(Node $toCompare)
    {
        // It only compares URIs, everything will be quit with false.
        if (true === $toCompare->isVariable()) {
            return $this->getName() == $toCompare->getName();
        }

        return false;
    }

    /**
     * @return string URI of the node.
     */
    public function getName()
    {
        return $this->name;
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
    public function isVariable()
    {
        return true;
    }

    /**
     * @return string
     */
    public function toNQuads()
    {
        return $this->name;
    }
}
