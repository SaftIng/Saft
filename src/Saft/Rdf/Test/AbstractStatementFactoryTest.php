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

use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;

abstract class AbstractStatementFactoryTest extends TestCase
{
    /**
     * An abstract method which returns new instances.
     */
    abstract public function getInstance(): StatementFactory;

    abstract public function getNodeFactory(): NodeFactory;

    public function testCreateStatement()
    {
        $s = $this->getNodeFactory()->createNamedNode('http://s');
        $p = $this->getNodeFactory()->createNamedNode('http://p');
        $o = $this->getNodeFactory()->createNamedNode('http://o');
        $g = $this->getNodeFactory()->createNamedNode('http://g');

        $stmt = $this->getInstance()->createStatement($s, $p, $o, $g);

        $this->assertTrue($stmt instanceof Statement);

        $this->assertEquals($s, $stmt->getSubject());
        $this->assertTrue($stmt->getSubject() instanceof Node);

        $this->assertEquals($p, $stmt->getPredicate());
        $this->assertTrue($stmt->getPredicate() instanceof Node);

        $this->assertEquals($o, $stmt->getObject());
        $this->assertTrue($stmt->getObject() instanceof Node);

        $this->assertEquals($g, $stmt->getGraph());
        $this->assertTrue($stmt->getGraph() instanceof Node);
    }
}
