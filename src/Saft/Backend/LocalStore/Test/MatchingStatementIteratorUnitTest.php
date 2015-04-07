<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\MatchingStatementIterator;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Backend\LocalStore\Store\NtriplesParser;
use Saft\Backend\LocalStore\Store\SyntaxException;

class MatchingStatementIteratorUnitTest extends \PHPUnit_Framework_TestCase
{
    // Used for temporary Ntriples files
    private $tempDirectory = null;

    public function setUp()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
    }

    public function tearDown()
    {
        if (!is_null($this->tempDirectory)) {
            TestUtil::deleteDirectory($this->tempDirectory);
        }
    }

    public function testMatchingWithConcreteNamedNode()
    {
        $filename = $this->fixtureFile('foaf.nt');
        $it = new MatchingStatementIterator($filename);
        $pattern = new StatementImpl(
            new NamedNodeImpl('http://www.example.com/joe#me'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
        $it->setPattern($pattern);
        $matches = [];
        foreach ($it as $statement) {
            array_push($matches, $statement);
        }
        $this->assertEquals(7, count($matches));
        $this->assertTrue(in_array(NtriplesParser::parseStatment('<http://www.example.com/joe#me> <http://xmlns.com/foaf/0.1/name> "Joe Bloggs"@en .'), $matches));
        $this->assertFalse(in_array(NtriplesParser::parseStatment('_:genid1 <http://xmlns.com/foaf/0.1/name> "Joe\'s Current Project" .'), $matches));
        $it->close();
    }

    public function testMatchingWithConcreteLiteral()
    {
        $filename = $this->fixtureFile('foaf.nt');
        $it = new MatchingStatementIterator($filename);
        $pattern = new StatementImpl(
            new VariableImpl('?s'),
            new VariableImpl('?p'),
            new LiteralImpl('Joe')
        );
        $it->setPattern($pattern);
        $matches = [];
        foreach ($it as $statement) {
            array_push($matches, $statement);
        }
        $this->assertEquals(1, count($matches));
        $this->assertTrue(in_array(NtriplesParser::parseStatment('<http://www.example.com/joe#me> <http://xmlns.com/foaf/0.1/firstName> "Joe" .'), $matches));
        $this->assertFalse(in_array(NtriplesParser::parseStatment('<http://www.example.com/joe#me> <http://xmlns.com/foaf/0.1/family_name> "Bloggs" .'), $matches));
        $it->close();
    }

    public function testKey()
    {
        $filename = $this->fixtureFile('foaf.nt');
        $it = new MatchingStatementIterator($filename);
        $pattern = new StatementImpl(
            new VariableImpl('?s'),
            new VariableImpl('?p'),
            new LiteralImpl('Joe')
        );
        $it->setPattern($pattern);
        $it->rewind();
        $this->assertEquals(9, $it->key());
        $it->close();
    }

    public function testMatchingWithSelectAllPattern()
    {
        $filename = $this->fixtureFile('foaf.nt');
        $it = new MatchingStatementIterator($filename);
        $pattern = self::createSelectAllPattern();
        $it->setPattern($pattern);
        $matches = [];
        foreach ($it as $statement) {
            array_push($matches, $statement);
        }
        $this->assertEquals(14, count($matches));
        $it->close();
    }

    public function testSyntaxError()
    {
        $filename = $this->fixtureFile('syntax_error.nt');
        $it = new MatchingStatementIterator($filename);
        $pattern = self::createSelectAllPattern();
        $it->setPattern($pattern);
        try {
            $it->rewind();
            while ($it->valid()) {
                $it->next();
            }
            $this->fail('Expected SyntaxException');
        } catch (SyntaxException $e) {
            $this->assertEquals(12, $e->getRow());
        }
        
        $it->close();
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorChecksForNull()
    {
        new MatchingStatementIterator(null);
    }

    public function testIsClosed()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename);
        $this->assertFalse($it->isClosed());
        $it->close();
        $this->assertTrue($it->isClosed());
    }

    public function testClose()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename, self::createSelectAllPattern());
        $it->rewind();
        $it->close();
        $this->assertTrue($it->isClosed());
    }

    /**
     * @expectedException \LogicException
     */
    public function testRewindChecksIfClosed()
    {
        $filename = $this->fixtureFile('foaf.nt');
        $it = new MatchingStatementIterator($filename, self::createSelectAllPattern());
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->rewind();
    }

    /**
     * @expectedException \LogicException
     */
    public function testRewindChecksIfPatternIsSet()
    {
        $filename = $this->fixtureFile('foaf.nt');
        $it = new MatchingStatementIterator($filename, null);
        // Will fail while closed
        $it->rewind();
    }

    /**
     * @expectedException \LogicException
     */
    public function testCurrentChecksIfClosed()
    {
        $filename = $this->fixtureFile('foaf.nt');
        $it = new MatchingStatementIterator($filename, self::createSelectAllPattern());
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->current();
    }

    /**
     * @expectedException \Exception
     */
    public function testCurrentChecksIfValid()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename);
        // Read to the end
        while ($it->valid()) {
            $it->next();
        }
        assert(!$it->valid());
        // No such element
        $it->current();
    }

    /**
     * @expectedException \LogicException
     */
    public function testKeyChecksIfClosed()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename, self::createSelectAllPattern());
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->key();
    }

    /**
     * @expectedException \Exception
     */
    public function testKeyChecksIfValid()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename, self::createSelectAllPattern());
        $it->rewind();
        // Read to the end
        while ($it->valid()) {
            $it->next();
        }
        assert(!$it->valid());
        // No such element
        $it->key();
    }

    /**
     * @expectedException \LogicException
     */
    public function testNextChecksIfClosed()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename, self::createSelectAllPattern());
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->next();
    }

    /**
     * @expectedException \Exception
     */
    public function testNextChecksIfValid()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename, self::createSelectAllPattern());
        // Read to the end
        while ($it->valid()) {
            $it->next();
        }
        assert(!$it->valid());
        // No such element
        $it->next();
    }

    /**
     * @expectedException \LogicException
     */
    public function testValidChecksIfClosed()
    {
        $filename = $this->fixtureFile('empty.nt');
        $it = new MatchingStatementIterator($filename);
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->valid();
    }

    private static function createSelectAllPattern()
    {
        return new StatementImpl(
            new VariableImpl('?s'),
            new VariableImpl('?p'),
            new VariableImpl('?o')
        );
    }

    private function fixtureFile($filename)
    {
        return __DIR__ . DIRECTORY_SEPARATOR
            . 'Fixture' . DIRECTORY_SEPARATOR . $filename;
    }
}
