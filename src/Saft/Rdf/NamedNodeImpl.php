<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Rdf;

class NamedNodeImpl extends AbstractNamedNode
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @param string $uri the URI of the node
     *
     * @throws \Exception if parameter $value is not a valid URI
     */
    public function __construct($uri)
    {
        if (null == $uri || false == is_string($uri) || false == $this->simpleCheckURI($uri)) {
            throw new \Exception('Parameter $uri is not a valid URI: '.$uri);
        }

        $this->uri = $uri;
    }

    /**
     * @return string URI of the node
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are not checked. Instead, only
     * characters disallowed an all URIs lead to a rejection of the check. Use this function, if you need a
     * basic check and if performance is an issuse. In case you need a more precise check, that function is
     * not recommended.
     *
     * @param string $string string to check if its a URI or not
     *
     * @return bool true if given string is a valid URI, false otherwise
     */
    protected function simpleCheckURI($string)
    {
        $regEx = '/^([a-z]{2,}:[^\s]*)$/';

        return 1 === preg_match($regEx, (string) $string);
    }
}
