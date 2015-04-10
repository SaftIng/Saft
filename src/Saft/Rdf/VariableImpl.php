<?php

namespace Saft\Rdf;

class VariableImpl implements Variable
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param  mixed      $value optional The Name of the variable. If not given, a random hash will be used
     *                                    later on.
     * @param  string     $lang  optional Will be ignore because a Variable has no language.
     * @throws \Exception                 If parameter $value is not a valid URI.
     */
    public function __construct($value = null, $lang = null)
    {
        // $value is a variable, like ?s
        if (null !== $value && true === is_string($value) && '?' == substr($value, 0, 1)) {
            $this->value = substr($value, 1);
            
        // $value is null, means we have to generate a random variable name
        } elseif (null === $value) {
            $variable = hash('sha1', microtime(true) . rand(0, time()));
            $this->value = substr($variable, 0, 10);
        } else {
            throw new \Exception('Parameter $value is neither a string with ?-prefix nor null.');
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
        return '?' . $this->getValue();
    }

    /**
     * @throws \LogicException always, because a variable is a pattern
     */
    public function matches(Node $pattern)
    {
        throw new \LogicException('A pattern can\'t matches another pattern.');
    }
}
