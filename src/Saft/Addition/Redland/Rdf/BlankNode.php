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

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\AbstractBlankNode;

/**
 * @deprecated
 */
class BlankNode extends AbstractBlankNode
{
    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    public function __construct($redlandNode)
    {
        $this->redlandNode = $redlandNode;
    }

    public function getBlankId()
    {
        return librdf_node_get_blank_identifier($this->redlandNode);
    }

    public function getRedlandNode()
    {
        return $this->redlandNode;
    }
}
