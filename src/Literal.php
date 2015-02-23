<?php
namespace Saft\Rdf;

abstract class Literal implements Node
{
    public function isConcrete()
    {
        return true;
    }

    public function isLiteral()
    {
        return true;
    }

    public function isNamed()
    {
        return false;
    }

    public function isBlank()
    {
        return false;
    }

    public function isVariable()
    {
        return false;
    }

    abstract public function getLiteralValue ();
    abstract public function getDatatype ();
    abstract public function getLanguage ();

    abstract public function equals($tocompare);

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

    public function __toString()
    {
        return $this->getLiteralValue();
    }
}
