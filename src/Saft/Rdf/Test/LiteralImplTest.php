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

namespace Saft\Rdf\Test;

use Saft\Rdf\LiteralImpl;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactoryImpl;

class LiteralImplTest extends AbstractLiteralTest
{
    /**
     * Return a new instance of LiteralImpl.
     */
    public function newInstance($value, Node $datatype = null, $lang = null)
    {
        return new LiteralImpl($value, $datatype, $lang);
    }

    public function getNodeFactory()
    {
        return new NodeFactoryImpl();
    }
}
