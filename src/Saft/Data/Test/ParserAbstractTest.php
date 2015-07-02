<?php

namespace Saft\Data\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;
use Saft\Addition\Redland\Data\Parser as RedlandParser;

abstract class ParserAbstractTest extends TestCase
{
    /**
     * @return Parser
     */
    abstract protected function newInstance();

    /*
     * Tests for getSupportedSerializations
     */

    // TODO what else can we test here?
    public function testGetSupportedSerializations()
    {
        $this->assertTrue(is_array($this->newInstance()->getSupportedSerializations()));
    }

    /*
     * Tests for parseStreamToIterator
     */

    // we load here the content of a turtle file and transform it into an StatementIterator instance.
    // afterwards we check if the read data are the same as expected.
    public function testParseStreamToIteratorTurtleFile()
    {
        $this->fixture = $this->newInstance();

        // load iterator for a turtle file
        $inputStream = dirname(__FILE__) .'/../resources/example.ttl';
        $iterator = $this->fixture->parseStreamToIterator($inputStream);

        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl('RDFS label')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl('http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    'RDFS label with language tag',
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                    'en'
                )
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#comment'),
                new LiteralImpl("\n    Multi line comment\n    ")
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://saft/example/component'),
                new NamedNodeImpl("http://saft/example/geo")
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foobar'),
                new NamedNodeImpl('http://saft/example/component'),
                new NamedNodeImpl("http://saft/example/time")
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/dataset'),
                new NamedNodeImpl('http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    "RDFS label with datatype",
                    new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string')
                )
            ),
        ));

        $this->assertEquals($statementIteratorToCheckAgainst, $iterator);
    }

    /*
     * Tests for parseStringToIterator
     */

    public function testParseStringToIteratorTurtleString()
    {
        $xsdString = new NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string');

        $fixture = $this->newInstance();

        $testString = '@prefix ex: <http://saft/example/> .
            ex:Foo  ex:knows ex:Bar ; ex:name  "Foo"^^<'. $xsdString .'> .
            ex:Bar  ex:name  "Bar"^^<'. $xsdString .'> .';

        // build StatementIterator to check against
        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foo'),
                new NamedNodeImpl('http://saft/example/knows'),
                new NamedNodeImpl('http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Foo'),
                new NamedNodeImpl('http://saft/example/name'),
                new LiteralImpl('Foo', $xsdString)
            ),
            new StatementImpl(
                new NamedNodeImpl('http://saft/example/Bar'),
                new NamedNodeImpl('http://saft/example/name'),
                new LiteralImpl('Bar', $xsdString)
            ),
        ));

        $this->assertEquals($statementIteratorToCheckAgainst, $fixture->parseStringToIterator($testString));
    }

    /**
     * W3C Parser Test
     * @dataProvider providerTestData
     */
    public function testParseStreamToIterator($format, $type, $action, $result)
    {
        $this->fixture = $this->newInstance();
        if ($type=="positivesyntax") {
            try {
                $iterator = $this->fixture->parseStreamToIterator($action, null, $format);
                $this->assertTrue(true);
            } catch (\Exception $e) {
                $this->fail($e);
            }

        } elseif ($type =="negativesyntax" or $type == "negativeeval") {
            $this->setExpectedException('\Exception');
            try {
                $iterator = $this->fixture->parseStreamToIterator($action, null, $format);
                $this->fail("Erwartete Exception wurde nicht geworfen");
            } catch (\Exception $e) {
                $this->assertTrue(true);
            }

        } elseif ($type == "eval") {
            try {
                $referenzParser = new RedlandParser();
                $expected = $this->fixture->parseStreamToIterator($action, null, $format);
                $actual = $referenzParser->parseStreamToIterator($result, null, 'ntriple');
                $this->assertStatementIteratorEquals($expected, $action);
            } catch (\Exception $e) {
                $this->fail($e);
            }
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
                if (strpos($testType, "NegativeEval") != false) {
                    //Type: NEGATIVEEVAL
                    $zeile = fgets($manifest, 4096);
                    $action = "";
                    $result = "";
                    while (substr($zeile, -2, 1)!='.') {
                        if (preg_match('~mf:action~', $zeile)) {
                            $temp = trim(str_replace('mf:action', "", $zeile));
                            $action = $testPath.trim(str_replace("<", "", str_replace("> ;", "", $temp)));
                        }
                        $zeile = fgets($manifest, 4096);
                    }
                    //add Testcase to TestArray
                    $type = "negativeeval";
                    $temparray = array($format,$type,$action,$result);
                    array_push($testDataArray, $temparray);
                } elseif (strpos($testType, "Eval")!= false) {
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
                } elseif (strpos($testType, "NegativeSyntax")!= false) {
                    //Testtype: //NEGATIVESYNTAX
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
                    $type = "negativesyntax";
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
                        } elseif (strpos($testType, "NegativeSyntax")!= false) {
                            $action = "";
                            $result = "";
                            $action = "";
                            $result = "";
                            while (true) {
                                if (preg_match('~mf:action~', $nextline)) {
                                    $temp = trim(str_replace("mf:action", "", $nextline));
                                    $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~", "", $temp));
                                    $action = $testPath.substr($action, 0, -3);

                                    //add to Testarray
                                    $type = "negativesyntax";
                                    $temparray = array($format,$type,$action,$result);
                                    array_push($testDataArray, $temparray);
                                    break;
                                } else {
                                    $nextline = fgets($manifest, 4096);
                                }
                            }
                        }
                    } elseif (preg_match('~ .*?;~', $nextline)) {
                        //Case: Turtle
                        preg_match('~rdft:[a-zA-Z0-9]*~', $nextline, $match);
                        $testType = trim(str_replace("rdft:", "", $match[0]));
                        if (strpos($testType, "NegativeSyntax")!= false) {
                            $action = "";
                            $result = "";
                            while (true) {
                                if (preg_match('~mf:action~', $nextline)) {
                                    $temp = trim(str_replace("mf:action", "", $nextline));
                                    $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~", "", $temp));
                                    $action = $testPath.substr($action, 0, -3);

                                    //add to Array
                                    $type = "negativesyntax";
                                    $temparray = array($format,$type,$action,$result);
                                    array_push($testDataArray, $temparray);
                                    break;

                                } else {
                                    $nextline = fgets($manifest, 4096);
                                }
                            }
                        } elseif (strpos($testType, "PositiveSyntax")!= false) {
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
