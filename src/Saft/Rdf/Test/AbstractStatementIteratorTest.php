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
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;

abstract class AbstractStatementIteratorTest extends TestCase
{
    /**
     * @param array $statements
     *
     * @return StatementIterator
     */
    abstract public function createInstanceWithArray(array $statements): StatementIterator;

    abstract public function getNodeFactory(): NodeFactory;

    abstract public function getStatementFactory(): StatementFactory;

    /*
     * Tests for constructor
     */

    public function testConstructorValidList()
    {
        // empty array must be fine
        $this->fixture = $this->createInstanceWithArray([]);
        $this->assertTrue($this->fixture instanceof StatementIterator);
    }

    public function testConstructorInvalidList()
    {
        // expect exception, because array contains non-Statement instance
        $this->expectException('\Exception');

        $this->fixture = $this->createInstanceWithArray([1]);
    }

    public function testConstructorListWithPattern()
    {
        $this->fixture = $this->createInstanceWithArray([
            $this->getStatementFactory()->createStatement(
                $this->getNodeFactory()->createNamedNode('http://s1'),
                $this->getNodeFactory()->createNamedNode('http://p'),
                $this->getNodeFactory()->createAnyPattern()
            ),
            $this->getStatementFactory()->createStatement(
                $this->getNodeFactory()->createNamedNode('http://s1'),
                $this->getNodeFactory()->createNamedNode('http://p'),
                $this->getNodeFactory()->createAnyPattern()
            ),
            $this->getStatementFactory()->createStatement(
                $this->getNodeFactory()->createNamedNode('http://s2'),
                $this->getNodeFactory()->createNamedNode('http://p'),
                $this->getNodeFactory()->createAnyPattern()
            ),
        ]);

        $i = 0;
        foreach ($this->fixture as $stmt) {
            ++$i;

            $this->assertTrue(in_array($stmt->getSubject(), ['http://s1', 'http://s2']));
        }

        $this->assertEquals(2, $i);
    }

    /*
     * Tests key
     */

    public function testKey()
    {
        $this->fixture = $this->createInstanceWithArray([
            $this->getStatementFactory()->createStatement(
                $this->getNodeFactory()->createNamedNode('http://s1'),
                $this->getNodeFactory()->createNamedNode('http://p'),
                $this->getNodeFactory()->createAnyPattern()
            ),
            $this->getStatementFactory()->createStatement(
                $this->getNodeFactory()->createNamedNode('http://s1'),
                $this->getNodeFactory()->createNamedNode('http://p'),
                $this->getNodeFactory()->createAnyPattern()
            ),
            $this->getStatementFactory()->createStatement(
                $this->getNodeFactory()->createNamedNode('http://s2'),
                $this->getNodeFactory()->createNamedNode('http://p'),
                $this->getNodeFactory()->createAnyPattern()
            ),
        ]);

        $this->assertEquals(0, $this->fixture->key());
        $this->fixture->next();
        $this->assertEquals(1, $this->fixture->key());
    }

    /*
     * Tests iteration
     */

    public function testIterationWithForeachLoop()
    {
        $nodeFactory = new NodeFactoryImpl();
        $rdfLangString = $nodeFactory->createNamedNode(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'
        );
        $statements = [
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('foobar', $rdfLangString, 'en')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('42')
            ),
        ];

        $iterator = $this->createInstanceWithArray($statements);

        $i = 0;
        foreach ($iterator as $stmt) {
            $this->assertTrue($stmt instanceof Statement);
            $this->assertEquals('http://s/', $stmt->getSubject()->getUri());
            ++$i;
        }

        $this->assertEquals(3, $i);
    }

    /*
     * Tests toArray
     */

    public function testToArray()
    {
        $nodeFactory = new NodeFactoryImpl();
        $rdfLangString = $nodeFactory->createNamedNode(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'
        );
        $statements = [
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new NamedNodeImpl('http://o/')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('foobar', $rdfLangString, 'en')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://s/'),
                new NamedNodeImpl('http://p/'),
                new LiteralImpl('42')
            ),
        ];

        $this->assertEquals(
            [
                [
                    's' => 'http://s/',
                    'p' => 'http://p/',
                    'o' => 'http://o/',
                ],
                [
                    's' => 'http://s/',
                    'p' => 'http://p/',
                    'o' => 'foobar',
                ],
                [
                    's' => 'http://s/',
                    'p' => 'http://p/',
                    'o' => '42',
                ],
            ],
            $this->createInstanceWithArray($statements)->toArray()
        );
    }
}
