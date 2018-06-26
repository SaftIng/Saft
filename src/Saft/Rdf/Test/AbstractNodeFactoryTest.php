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

use Saft\Rdf\AnyPattern;
use Saft\Rdf\BlankNode;
use Saft\Rdf\Literal;
use Saft\Rdf\NamedNode;
use Saft\Rdf\NodeFactory;

abstract class AbstractNodeFactoryTest extends TestCase
{
    /**
     * An abstract method which returns new instances of NodeFactory.
     */
    abstract public function getInstance(): NodeFactory;

    public function testCreateNamedNodeShortenedUri()
    {
        $fixture = $this->getInstance();

        $node = $fixture->createNamedNode('foaf:Person');

        $this->assertTrue($node->isNamed());
        $this->assertEquals('http://xmlns.com/foaf/0.1/Person', $node->getUri());
    }

    public function testCreateNamedNodeExtendedUri()
    {
        $fixture = $this->getInstance();

        $node = $fixture->createNamedNode('http://xmlns.com/foaf/0.1/Person');
        $this->assertTrue($node instanceof NamedNode);
        $this->assertEquals('http://xmlns.com/foaf/0.1/Person', $node->getUri());
    }

    public function testCreateNamedNode()
    {
        $this->assertTrue($this->getInstance()->createNamedNode('http://foo') instanceof NamedNode);
    }

    public function testCreateLiteral()
    {
        $this->assertTrue($this->getInstance()->createLiteral('ff') instanceof Literal);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Argument datatype has to be a named node.
     */
    public function testCreateLiteralDatatypeNotNamedNode()
    {
        $this->getInstance()->createLiteral('ff', $this->getInstance()->createAnyPattern('ff'));
    }

    public function testCreateBlankNode()
    {
        $this->assertTrue($this->getInstance()->createBlankNode('ff') instanceof BlankNode);
    }

    public function testCreateAnyPattern()
    {
        $this->assertTrue($this->getInstance()->createAnyPattern('ff') instanceof AnyPattern);
    }
}
