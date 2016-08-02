<?php

namespace Saft\Test;

/**
 * That constraint tries to match a given regex on a given string. If the regex matches, the constraint is true,
 * false otherwise.
 */
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
