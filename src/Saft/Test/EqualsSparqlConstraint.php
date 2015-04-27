<?php

namespace Saft\Test;

class EqualsSparqlConstraint extends \PHPUnit_Framework_Constraint
{
    protected $isEqual;
    protected $expected;
    protected $actual;

    public function __construct($expected)
    {
        $expected = preg_replace('/\s+/', '', $expected);
        $this->isEqual = new \PHPUnit_Framework_Constraint_IsEqual($expected);
    }

    public function matches($actual)
    {
        $actual = preg_replace('/\s+/', '', $actual);
        return $this->isEqual->evaluate($actual);
    }

    public function toString()
    {
        return 'is a similar SPARQL query';
    }
}
