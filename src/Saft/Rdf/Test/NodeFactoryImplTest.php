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

use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeFactoryImpl;

class NodeFactoryImplTest extends AbstractNodeFactoryTest
{
    /**
     * An abstract method which returns new instances of NodeFactory.
     */
    public function getInstance(): NodeFactory
    {
        return new NodeFactoryImpl(new CommonNamespaces());
    }
}
