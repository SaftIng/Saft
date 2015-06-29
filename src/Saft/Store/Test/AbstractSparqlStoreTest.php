<?php

namespace Saft\Store\Test;

use Saft\Rdf\AnyPatternImpl;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\EmptyResult;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Test\RegexMatchConstraint;
use Saft\Test\TestCase;

class AbstractSparqlStoreTest extends TestCase
{
    /**
     * @var string
     */
    protected $regexLiteral = '"[a-z0-9]+"\^\^<[a-z0-9:\/#-\.]+>';
    
    /**
     * @var string
     */
    protected $regexPattern = '\?[a-z0-9]+';

    /**
     * @var string
     */
    protected $regexUri = '[a-z0-9:\/#-\.]+';
    
    public function setUp()
    {
        parent::setUp();

        $this->mock = $this->getMockForAbstractClass(
            '\Saft\Store\AbstractSparqlStore',
            array(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl()
            )
        );
    }

    /*
     * Helper methods
     */

    /**
     * Creates basic test statement. You can call it and decide if you need s, p, o and as AnyPatternImpl or
     * NamedNodeImpl. g can be both or null.
     */
    protected function getTestStatement($s = 'pattern', $p = 'pattern', $o = 'pattern', $g = null)
    {
        // s
        if ('pattern' == $s) {
            $s = new AnyPatternImpl();
        } elseif ('uri' == $s) {
            $s = new NamedNodeImpl('http://saft/test/s');
        }
        
        // p
        if ('pattern' == $p) {
            $p = new AnyPatternImpl();
        } elseif ('uri' == $p) {
            $p = new NamedNodeImpl('http://saft/test/p');
        }
        
        // o
        if ('pattern' == $o) {
            $o = new AnyPatternImpl();
        } elseif ('uri' == $o) {
            $o = new NamedNodeImpl('http://saft/test/o');
        }
        
        // g
        if ('pattern' == $g) {
            $g = new AnyPatternImpl();
            $stmt = new StatementImpl($s, $p, $o, $g);
        } elseif ('uri' == $g) {
            $g = new NamedNodeImpl('http://saft/test/g');
            $stmt = new StatementImpl($s, $p, $o, $g);
        } else { // null == $g
            $stmt = new StatementImpl($s, $p, $o);
        }
        
        return $stmt;
    }

    protected function getFilledTestArrayStatementIterator()
    {
        return new ArrayStatementIteratorImpl(array(
            $this->getTestStatement('uri', 'uri', 'uri')
        ));
    }

    /*
     * Tests for addStatements
     */

    public function testAddStatements2()
    {
        $statements = new ArrayStatementIteratorImpl(array(
            $this->getTestStatement('uri', 'uri', 'uri', 'uri'),
            $this->getTestStatement('uri', 'uri', 'uri', 'uri')
        ));
        
        /*
            check that query function gets a query which looks like: 
            INSERT DATA {
                Graph <http://saft/test/g1> { <http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>. }
                Graph <http://saft/test/g2> { <http://saft/test/s2> <http://saft/test/p2> <http://saft/test/o2>. }
            }
         */
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/INSERTDATA{Graph<'. $this->regexUri .'>{'. 
                    '<'. $this->regexUri .'><'. $this->regexUri .'><'. $this->regexUri .'>.'.
                '}Graph<'. $this->regexUri .'>{'. 
                    '<'. $this->regexUri .'><'. $this->regexUri .'><'. $this->regexUri .'>.'.
                '}}/si'
            ));

        // use the given graphUri
        $this->assertNull(
            $this->mock->addStatements($statements, $this->testGraph)
        );
    }

    public function testAddStatementsMultipleVariatonOfObjects()
    {
        /*
         * object is a number
         */
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new LiteralImpl(42); // will be handled as string, because no datatype given.
        $triple1 = new StatementImpl($subject1, $predicate1, $object1);

        /*
         * object is a literal
         */
        $object2 = new LiteralImpl('John'); // will be handled as string, because no datatype given.
        $triple2 = new StatementImpl($subject1, $predicate1, $object2);

        // Setup array statement iterator
        $statements = new ArrayStatementIteratorImpl(array($triple1, $triple2));        
        
        /*
            check that query function gets a query which looks like: 
            INSERT DATA { 
                Graph <http://saft/test/> {
                    <http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#string>. 
                }
                Graph <http://saft/test/> {
                    <http://saft/test/s1> <http://saft/test/p1> "John"^^<http://www.w3.org/2001/XMLSchema#string>. 
                }
            }
         */
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/INSERTDATA{Graph<'. $this->regexUri .'>{'. 
                    '<'. $this->regexUri .'><'. $this->regexUri .'>'. $this->regexLiteral .'.'.
                '}Graph<'. $this->regexUri .'>{'. 
                    '<'. $this->regexUri .'><'. $this->regexUri .'>'. $this->regexLiteral .'.'.
                '}}/si'
            ));
        
        // add test statements
        $this->mock->addStatements($statements, $this->testGraph);
    }

    // test if given graphUri is preferred.
    public function testAddStatementsWithGraphUri()
    {
        /*
            check that query function gets a query which looks like: 
            INSERT DATA { 
                Graph <http://saft/test/foograph> {
                    <http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.
            } }
         */
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/INSERTDATA{Graph<'. $this->regexUri .'>{'. 
                    '<'. $this->regexUri .'><'. $this->regexUri .'><'. $this->regexUri .'>.'.
                '}}/si'
            ));

        // use the given graphUri
        $this->assertNull(
            $this->mock->addStatements($this->getFilledTestArrayStatementIterator(), $this->testGraph)
        );
    }

    /*
     * Tests for deleteMatchingStatements
     */

    public function testDeleteMatchingStatements()
    {
        /*
            check that query function gets a query which looks like: 
            DELETE WHERE { 
                Graph <http://saft/test/g1> {
                    <http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1>.
                } 
            }
         */
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/DELETEWHERE{Graph<'. $this->regexUri .'>{'. 
                    '<'. $this->regexUri .'><'. $this->regexUri .'><'. $this->regexUri .'>.'.
                '}}/si'
            ));

        $this->mock->deleteMatchingStatements(
            $this->getTestStatement('uri', 'uri', 'uri', 'uri')
        );
    }

    /*
     * Tests for getMatchingStatements
     */

    public function testGetMatchingStatements()
    {
        /*
           check that query function gets a query which looks like: 
           SELECT ?s ?p ?o 
           FROM <http://saft/test/g1> 
           WHERE { 
                ?s ?p ?o 
                FILTER(str(?s)="http://saft/test/s1")
                FILTER(str(?p)="http://saft/test/p1")
                FILTER(str(?o)="http://saft/test/o1")
           }
        */
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/'.
                // select
                'SELECT'. $this->regexPattern . $this->regexPattern . $this->regexPattern .
                // from
                'FROM<'. $this->regexUri .'>'.
                // where
                'WHERE{'.
                    $this->regexPattern . $this->regexPattern . $this->regexPattern .
                    'FILTER\(str\('. $this->regexPattern .'\)="'. $this->regexUri .'"\)'.
                    'FILTER\(str\('. $this->regexPattern .'\)="'. $this->regexUri .'"\)'.
                    'FILTER\(str\('. $this->regexPattern .'\)="'. $this->regexUri .'"\)'.
                '}'.
                '/si'
            ));

        $result = $this->mock->getMatchingStatements(
            $this->getTestStatement('uri', 'uri', 'uri', 'uri')
        );

        $this->assertTrue($result->isEmptyResult());
    }

    /*
     * Tests for hasMatchingStatement
     */

    public function testHasMatchingStatement()
    {
        // check that query function gets a query which looks like: 
        // ASK { Graph <http://saft/test/g1> { 
        //      <http://saft/test/s1> <http://saft/test/p1> <http://saft/test/o1> . 
        // } }
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/ASK{Graph<'. $this->regexUri .'>{'. 
                    $this->regexPattern .'<'. $this->regexUri .'><'. $this->regexUri .'>.*'.
                '}}/i'
            ));

        $result = $this->mock->hasMatchingStatement(
            $this->getTestStatement('pattern', 'uri', 'uri', 'uri')
        );
        $this->assertNull($result);
    }

    /*
     * Tests for that the pattern-variable is recognized properly.
     */

    // subject is a pattern variable
    public function testPatternStatementSubjectIsPattern()
    {
        // check that query function gets a query which looks like: 
        // ASK { ?s1 <http://saft/test/p1> <http://saft/test/o1> . }
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/ASK{'. $this->regexPattern .'<'. $this->regexUri .'><'. $this->regexUri .'>.}/i'
            ));

        $result = $this->mock->hasMatchingStatement(
            $this->getTestStatement('pattern', 'uri', 'uri')
        );
        
        $this->assertNull($result);
    }

    // graph is a pattern variable
    public function testPatternStatementGraphIsPattern()
    {
        // check that the query function gets a query which looks like:
        // ASK { Graph ?g1 {?s1 <http://saft/test/p1> <http://saft/test/o1>} }
        $this->mock
            ->expects($this->once())
            ->method('query')
            ->with(new RegexMatchConstraint(
                '/ASK{Graph'. $this->regexPattern .'{'. 
                    $this->regexPattern .'<'. $this->regexUri .'><'. $this->regexUri .'>.*'.
                '}}/i'
            ));

        $result = $this->mock->hasMatchingStatement(
            $this->getTestStatement('pattern', 'uri', 'uri', 'pattern')
        );

        $this->assertNull($result);
    }
}
