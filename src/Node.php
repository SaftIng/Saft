<?php
namespace Saft\Rdf;

interface Node
{
    public function isConcrete();

    public function isLiteral();

    public function isNamed();

    public function isBlank();

    public function isVariable();

    public function equals($tocompare);

    public function toNT();

    public function __toString();
}
