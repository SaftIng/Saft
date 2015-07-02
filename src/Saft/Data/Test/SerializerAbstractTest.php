<?php

namespace Saft\Data\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;
use Saft\Addition\Redland\Data\Parser as RedlandParser;

abstract class SerializerAbstractTest extends TestCase
{
    /**
     * @return Serializer
     */
    abstract protected function newInstance();

    /*
     * Tests for serializeIteratorToStream
     */

    public function testSerializeIteratorToStreamAsNQuads()
    {
        // serialize $iterator to turtle
        $this->fixture = $this->newInstance();

        if (false === in_array('n-quads', $this->fixture->getSupportedSerializations())) {
            $this->markTestSkipped('Fixture does not support n-quads serialization.');
        }

        $iterator = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/2'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
        ));

        $filepath = tempnam(sys_get_temp_dir(), 'saft_');
        $testFile = fopen('file://' . $filepath, 'w+');

        $this->fixture->serializeIteratorToStream($iterator, $testFile, 'n-quads');

        // check
        $this->assertEquals(
            '<http://saft/example/> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .'. PHP_EOL .
            '<http://saft/example/2> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .',
            trim(file_get_contents($filepath))
        );
    }

    public function testSerializeIteratorToStreamAsNTriples()
    {
        // serialize $iterator to turtle
        $this->fixture = $this->newInstance();

        if (false === in_array('n-triples', $this->fixture->getSupportedSerializations())) {
            $this->markTestSkipped('Fixture does not support n-triples serialization.');
        }

        $iterator = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/2'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
        ));

        $filepath = tempnam(sys_get_temp_dir(), 'saft_');
        $testFile = fopen('file://' . $filepath, 'w+');

        $this->fixture->serializeIteratorToStream($iterator, $testFile, 'n-triples');

        // check
        $this->assertEquals(
            '<http://saft/example/> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .'. PHP_EOL .
            '<http://saft/example/2> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> '.
            '<http://saft/example/Foo> .',
            trim(file_get_contents($filepath))
        );
    }

    /*
     * @dataProvider providerTestData
     */
    public function testParseStreamToIterator($format, $type, $action, $result)
    {
        $this->fixture = $this->newInstance();

        //tempSerializePath
        $filepath = tempnam(sys_get_temp_dir(), 'saft_');
        $testFile = fopen('file://' . $filepath, 'w+');

        //parse action
        $referenzParser = new RedlandParser();
        $origialFile = $referenzParser->parseStreamToIterator($action, null, $format);

        //serialize action
        try {
            $this->fixture->serializeIteratorToStream($iterator, $testFile, $format);
        } catch (Exception $e) {
            $this->fail($e);
        }

        //parse serialized action
        $serializedFile = $referenzParser->parseStreamToIterator($testFile, null, $format);

        //Assertion between "parsed action" and "parsed serilized action"
        try {
            $this->assertStatementIteratorEquals($origialFile, $serializedFile);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    //DataProvider for W3C RDF Testcases
    public function providerTestData()
    {
      //-------------------Params--------------------------
        //Testcases Path
        $testPath = dirname(__FILE__) .'/../resources/testcases/TurtleTests/';
        $manifestName = "manifest.ttl";

        //Parserformat
        $format = "turtle";

        $testDataArray = array(); //format|type|datei|result

      //----------------Manifest.ttl Parser-----------------

        //read Manifest.ttl
        $manifest = fopen($testPath.$manifestName, "r");

        $testType="";
        $action = "";
        $result = "";
        while ($zeile = fgets($manifest, 4096)) {
            //Evaluation of Testcases
            if (preg_match('~<#.*?> .*? ;~', $zeile)) {
                //parse Testtype
                preg_match('~rdft:[a-zA-Z0-9]*~', $zeile, $match);
                $testType = trim(str_replace("rdft:", "", $match[0]));
                if (strpos($testType, "Eval")!= false) {
                    //Type: EVAL
                    $zeile = fgets($manifest, 4096);
                    $action = "";
                    $result = "";

                    while (substr($zeile, -2, 1)!='.') {
                        if (preg_match('~mf:action~', $zeile)) {
                            $temp = trim(str_replace("mf:action", "", $zeile));
                            $action = $testPath.trim(str_replace("<", "", str_replace("> ;", "", $temp)));
                        } elseif (preg_match('~mf:result~', $zeile)) {
                            $temp = trim(str_replace("mf:result", "", $zeile));
                            $result = $testPath.trim(str_replace("<", "", str_replace("> ;", "", $temp)));
                        }
                        $zeile = fgets($manifest, 4096);
                    }
                    //add Testcase to TestArray
                    $type = "eval";
                    $temparray = array($format,$type,$action,$result);
                    array_push($testDataArray, $temparray);
                } elseif (strpos($testType, "PositiveSyntax")!= false) {
                    //Testtype: POSTIVESYNTAX
                    $zeile = fgets($manifest, 4096);
                    $action = "";
                    $result = "";
                    while (substr($zeile, -2, 1)!='.') {
                        if (preg_match('~mf:action~', $zeile)) {
                            $temp = trim(str_replace("mf:action", "", $zeile));
                            $action = $testPath.trim(str_replace("<", "", str_replace("> ;", "", $temp)));
                        }
                        $zeile = fgets($manifest, 4096);
                    }
                    //add Testcase to TestArray
                    $type = "positivesyntax";
                    $temparray = array($format,$type,$action,$result);
                    array_push($testDataArray, $temparray);
                }
            } else {
                //Special case (prepared für turtle and xml manifest)
                if (preg_match('~<#[a-zA-Z0-9_-]*?>~', $zeile)) {
                    $nextline = fgets($manifest, 4096);
                    //Case: XML
                    if (preg_match('~<#[a-zA-Z0-9_-]*?> a rdft.*~', $zeile)) {
                        preg_match('~rdft:[a-zA-Z0-9]*~', $zeile, $match);
                        $testType = trim(str_replace("rdft:", "", $match[0]));

                        //ignore comments
                        if (substr($zeile, 0, 1)!='#') {
                        } elseif (strpos($testType, "Eval")!= false) {
                            $action = "";
                            $result = "";
                            while (true) {
                                if (preg_match('~mf:action~', $nextline)) {
                                    $temp = trim(str_replace("mf:action", "", $nextline));
                                    $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~", "", $temp));
                                    $action = $testPath.substr($action, 0, -2);
                                } elseif (preg_match('~mf:result~', $nextline)) {
                                    $temp = trim(str_replace("mf:result", "", $nextline));
                                    $result = trim(preg_replace("~^[a-zA-Z0-9\.-_]~", "", $temp));
                                    $result = $testPath.substr($result, 0, -3);

                                    //add to TestArray
                                    $type = "eval";
                                    $temparray = array($format,$type,$action,$result);
                                    array_push($testDataArray, $temparray);
                                    break;
                                }
                                $nextline = fgets($manifest, 4096);
                            }
                        }
                    } elseif (preg_match('~ .*?;~', $nextline)) {
                        //Case: Turtle
                        preg_match('~rdft:[a-zA-Z0-9]*~', $nextline, $match);
                        $testType = trim(str_replace("rdft:", "", $match[0]));
                        if (strpos($testType, "PositiveSyntax")!= false) {
                            $action = "";
                            $result = "";
                            while (true) {
                                if (preg_match('~mf:action~', $nextline)) {
                                    $temp = trim(str_replace("mf:action", "", $nextline));
                                    $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~", "", $temp));
                                    $action = $testPath.substr($action, 0, -3);

                                    //add to Testarray
                                    $type = "positivesyntax";
                                    $temparray = array($format,$type,$action,$result);
                                    array_push($testDataArray, $temparray);
                                    break;

                                } else {
                                    $nextline = fgets($manifest, 4096);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $testDataArray;
    }
}
