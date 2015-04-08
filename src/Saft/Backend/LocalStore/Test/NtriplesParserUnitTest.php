<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\NtriplesParser;
use Saft\Backend\LocalStore\Store\SyntaxException;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;

class NtriplesParserUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testParseStatementTellsSyntaxErrorAtTheRightPosition()
    {
        // Error on the whole line
        $this->checkSyntaxError(' .');
        // Subject is malformed
        $this->checkSyntaxError('  _ <http://example.net> "xyz\\x" .', 2);
        // Predicate is malformed (must be a URI)
        $this->checkSyntaxError('_:foo _:bar "xyz\\x" .', 6);
        // Object is malformed (missing closing ")
        $this->checkSyntaxError('_:foo <http://example.net> "xyz .', 27);
        // Error in object value: \x is not an valid escape char
        $this->checkSyntaxError('_:foo <http://example.net> "xyz\\x" .', 31);
        // Object is malformed
        $this->checkSyntaxError('_:foo <http://example.net> _ .', 27);
    }

    public function checkSyntaxError($line, $column = SyntaxException::UNDEFINED)
    {
        try {
            NtriplesParser::parseStatment($line);
            $this->fail('Expected syntax error for "' . $line . '"');
        } catch (SyntaxException $e) {
            $this->assertEquals($column, $e->getColumn());
        }
    }

    public function testParseStatement()
    {
        $expected = new StatementImpl(
            new NamedNodeImpl('http://example.net/foo'),
            new NamedNodeImpl('http://example.net/bar'),
            new LiteralImpl('Welcome', 'en-US')
        );
        $actual = NtriplesParser::parseStatment(
            '<http://example.net/foo> '
            . '<http://example.net/bar> '
            . '"Welcome"@en-US .'
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Saft\Backend\LocalStore\Store\SyntaxException
     */
    public function testParseSubjectChecksSyntax()
    {
        NtriplesParser::parseSubject('no-subject');
    }

    public function testParseSubjectUri()
    {
        $expected = new NamedNodeImpl('http://example.net/foo');
        $actual = NtriplesParser::parseSubject('<http://example.net/foo>');
        $this->assertEquals($expected, $actual);
    }

    public function testParseSubjectBlank()
    {
        $expected = new BlankNodeImpl('foo');
        $actual = NtriplesParser::parseSubject('_:foo');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Saft\Backend\LocalStore\Store\SyntaxException
     */
    public function testParsePredicateChecksSyntax()
    {
        NtriplesParser::parsePredicate('no-predicate');
    }

    public function testParsePredicate()
    {
        $expected = new NamedNodeImpl('http://example.net/foo');
        $actual = NtriplesParser::parsePredicate('<http://example.net/foo>');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Saft\Backend\LocalStore\Store\SyntaxException
     */
    public function testParseObjectChecksSyntax()
    {
        NtriplesParser::parseObject('no-object');
    }

    public function testParseObjectLiteral()
    {
        $expected = new LiteralImpl('foo');
        $actual = NtriplesParser::parseObject('"foo"');
        $this->assertEquals($expected, $actual);
    }

    /*
    public function testParseObjectLiteralWithDataType()
    {
        //TODO data type
        $expected = new LiteralImpl('123', 'xsd:integer');
        $actual = NtriplesParser::parseObject('"123"^^<http://www.w3.org/2001/XMLSchema#integer>');
        $this->assertEquals($expected, $actual);
    }*/

    public function testParseObjectLiteralWithLang()
    {
        $expected = new LiteralImpl('Welcome', 'en-US');
        $actual = NtriplesParser::parseObject('"Welcome"@en-US');
        $this->assertEquals($expected, $actual);
    }

    public function testParseObjectUri()
    {
        $expected = new NamedNodeImpl('http://example.net/foo');
        $actual = NtriplesParser::parseObject('<http://example.net/foo>');
        $this->assertEquals($expected, $actual);
    }

    public function testParseObjectBlank()
    {
        $expected = new BlankNodeImpl('foo');
        $actual = NtriplesParser::parseObject('_:foo');
        $this->assertEquals($expected, $actual);
    }
}
