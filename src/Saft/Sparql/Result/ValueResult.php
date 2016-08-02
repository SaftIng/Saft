<?php

namespace Saft\Sparql\Result;

interface ValueResult extends Result
{
    /**
     * @return mixed
     */
    public function getValue();
}
