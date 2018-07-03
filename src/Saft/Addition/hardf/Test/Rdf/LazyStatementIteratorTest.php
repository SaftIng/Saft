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

namespace Saft\Addition\hardf\Test\Rdf;

use Saft\Addition\hardf\Rdf\LazyStatementIterator;
use Saft\Rdf\Statement;
use Saft\Rdf\Test\TestCase;

class LazyStatementIteratorTest extends TestCase
{
    protected function getInstance(): LazyStatementIterator
    {
        return new LazyStatementIterator(
            __DIR__.'/../resources/n-triples-example.nt',
            'n-triples',
            $this->nodeFactory,
            $this->statementFactory
        );
    }

    public function testCurrent()
    {
        $iterator = $this->getInstance();

        $this->assertEquals(
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://example.org/show/218'),
                $this->nodeFactory->createNamedNode('http://www.w3.org/2000/01/rdf-schema#label'),
                $this->nodeFactory->createLiteral('That Seventies Show')
            ),
            $iterator->current()
        );

        $this->assertEquals(
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://example.org/show/218'),
                $this->nodeFactory->createNamedNode('http://www.w3.org/2000/01/rdf-schema#label'),
                $this->nodeFactory->createLiteral('That Seventies Show')
            ),
            $iterator->current()
        );
    }

    // test normal usage of the function current, key, next and valid
    public function testCurrentKeyNextValid()
    {
        $iterator = $this->getInstance();

        $this->assertTrue($iterator->valid());
        $this->assertTrue($iterator->current() instanceof Statement);

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $this->assertEquals(
            $this->statementFactory->createStatement(
                $this->nodeFactory->createNamedNode('http://example.org/show/218'),
                $this->nodeFactory->createNamedNode('http://www.w3.org/2000/01/rdf-schema#label'),
                $this->nodeFactory->createNamedNode('http://foobar/')
            ),
            $iterator->current()
        );

        $iterator->next();
        $this->assertEquals(3, $iterator->key());
        $iterator->next();
        $this->assertEquals(4, $iterator->key());
        $iterator->next();
        $iterator->next();
        // end of file reached

        $this->assertNull($iterator->current());
        $this->assertFalse($iterator->valid());
    }

    public function testToArray()
    {
        $this->assertEquals(
            [
                [
                    's' => 'http://example.org/show/218',
                    'p' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'o' => 'That Seventies Show',
                ],
                [
                    's' => 'http://example.org/show/218',
                    'p' => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'o' => 'http://foobar/',
                ],
                [
                    's' => 'http://example.org/show/218',
                    'p' => 'http://example.org/show/localName',
                    'o' => 'That Seventies Show',
                ],
                [
                    's' => 'http://example.org/show/218',
                    'p' => 'http://example.org/show/localName',
                    'o' => 'Cette Série des Années Septante',
                ],
                [
                    's' => 'http://example.org/#spiderman',
                    'p' => 'http://example.org/text',
                    'o' => "Multi-line\nliteral with many quotes (\"\"\"\"\")\nand two apostrophes ('').",
                ],
            ],
            $this->getInstance()->toArray()
        );
    }
}
