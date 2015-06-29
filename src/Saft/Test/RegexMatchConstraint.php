<?php

namespace Saft\Test;

class RegexMatchConstraint extends \PHPUnit_Framework_Constraint
{
    protected $regex;

    public function __construct($regex)
    {
        parent::__construct();
        
        $this->regex = $regex;
    }

    public function matches($toCheck)
    {
        return 1 == preg_match($this->regex, preg_replace('/\s+/', '', $toCheck));
    }

    public function toString()
    {
        return 'matches the given Regex: '. $this->regex;
    }
}
