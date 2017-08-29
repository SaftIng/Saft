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

namespace Saft\Rdf;

class BlankNodeImpl extends AbstractBlankNode
{
    protected $blankId;

    /*
     * @param string $blankId
     */
    public function __construct($blankId)
    {
        if (is_string($blankId)) {
            $this->blankId = $blankId;
        } else {
            throw new \Exception('Blank nodes have to have a string as $blankId.');
        }
    }

    public function getBlankId()
    {
        return $this->blankId;
    }
}
