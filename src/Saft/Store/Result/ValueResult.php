<?php

namespace Saft\Store\Result;

interface ValueResult extends Result
{
    /**
     * @return mixed
     */
    public function getValue();
}
