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

namespace Saft\Sparql\Result;

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
