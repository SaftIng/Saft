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

namespace Saft\Addition\Redland\Tests\Rdf;

use Saft\Addition\Redland\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\Test\NodeFactoryAbstractTest;

class NodeFactoryTest extends NodeFactoryAbstractTest
{
    /**
     * An abstract method which returns new instances of NodeFactory
     */
    public function getFixture()
    {
        return new NodeFactory(new NodeUtils());
    }

    /**
     *
     */
    public function setUp()
    {
        if (false === extension_loaded('redland')) {
            $this->markTestSkipped('Extension redland is not loaded.');
        }

        parent::setUp();
    }
}
