<?php
namespace Saft\Rdf;

abstract class NamedNode implements Node
{
    public function isConcrete()
    {
        return true;
    }

    public function isLiteral()
    {
        return false;
    }

    public function isNamed()
    {
        return true;
    }

    public function isBlank()
    {
        return false;
    }

    public function isVariable()
    {
        return false;
    }

    abstract public function getUri ();

    abstract public function equals($tocompare);

    public function toNT()
    {
        return '<' . $this->getUri() . '>';
    }

    public function __toString()
    {
        return $this->getUri();
    }
}
