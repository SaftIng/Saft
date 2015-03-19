<?php

namespace Saft\Rdf;

class NamedNodeImpl extends AbstractNamedNode
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param mixed $value The URI of the node.
     * @param string $lang optional Will be ignore because a NamedNode has no language.
     * @throws \Exception If parameter $value is not a valid URI.
     */
    public function __construct($value, $lang = null)
    {
        if (true === self::check($value)
            || null === $value
            || true === self::isVariable($value)) {
            $this->value = $value;
        } else {
            throw new \Exception('Parameter $value is not a valid URI.');
        }
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
     * @return string URI of the node.
     */
    public function getValue()
    {
        return $this->value;
    }
}
