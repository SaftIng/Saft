<?php

namespace Saft\Store\Result;

/**
 * This class represents a result set.
 */
interface SetResult extends \Iterator, Result
{
    /**
     * @return array
     */
    public function getVariables();

    /**
     * @param array $variables
     */
    public function setVariables($variables);
}
