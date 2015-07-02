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

    /**
     * @param string $format Format des Tests z.B. turtle, ntriple
     * @param string $type Der jeweilige Testtyp z.B. eval, positivesyntax
     * @param string $action Objekt welches geparsed wird
     * @param string $result Vergleichsobjekt
     * @dataProvider providerTestData
     */
    public function testParseStreamToIterator($format,$type,$action,$result)
    {
      $this->fixture = $this->newInstance();

      //Ist das notwenidig?
      if (false === in_array('n-triples', $this->fixture->getSupportedSerializations())) {
          $this->markTestSkipped('Fixture does not support n-triples serialization.');
      }
      //Ausgabedatei definieren
      $filepath = tempnam(sys_get_temp_dir(), 'saft_');
      $testFile = fopen('file://' . $filepath, 'w+');

      //SUBJECT UNDER TEST
      //Parsen der Datei
      $referenzParser = new RedlandParser();
      $origialFile = $referenzParser->parseStreamToIterator($action,null,$format);

      //Serialisieren der Datei
      try
      {
        $this->fixture->serializeIteratorToStream($iterator, $testFile, $format);
      }
      catch (Exception $e)
      {
        $this->fail($e);
      }

      //Parsen dersoeben erzeugten Testdatei
      $serializedFile = $referenzParser->parseStreamToIterator($testFile,null,$format);

      //Assertion zwischen geparster Testdatei und geparster OriginalDatei
      try
      {
        $this->assertStatementIteratorEquals($origialFile, $serializedFile);
      }
      catch(\Exception $e)
      {
        $this->fail($e);
      }
    }

    //DataProvider für W3C RDF Testcases
    public function providerTestData()
    {
      //-------------------Parameter--------------------------
      $testPath = dirname(__FILE__) .'/../resources/testcases/TurtleTests/';
      $manifestName = "manifest.ttl";

      $format = "turtle";

      $prefixArray = array();//Array für Prefixes
      $testArray = array(); //Aufbau: format|type|datei|result

      //-----------------Funktionalität-----------------------

      //Einlesen der Datei
      $manifest = fopen($testPath.$manifestName,"r");

      //Auswerten der Datei (Regex)
      $testType="";
      $action = "";
      $result = "";
      while($zeile = fgets($manifest,4096))
      {
          /*
          //Auswerten des Prefix
          if(preg_match('~@prefix.*<.*>~',$zeile))
          {
            $temp = str_replace("@prefix","",$zeile);
            //
            preg_match('~[a-zA-Z0-9]*:~',$temp,$match);
            $param1 = trim(str_replace(":","",$match[0]));
            preg_match('~<.*?>~',$temp,$match);
            $param2 = trim($match[0]);
            $temparray = array($param1,$param2);
            array_push($prefixArray,$temparray);
          }
          */
          //Auswerten der Tests
          if(preg_match('~<#.*?> .*? ;~',$zeile))
          {
            //Auslesen des TestTypes
            preg_match('~rdft:[a-zA-Z0-9]*~',$zeile,$match);
            $testType = trim(str_replace("rdft:","",$match[0]));
            //$echo $testType;
      /*//NEGATIVEEVAL
            if(strpos($testType,"NegativeEval") != false)
            {
              //nachfolgende Zeilen Einlesen bis zum nächsten Punkt
              $zeile = fgets($manifest,4096);
              $action = "";
              $result = "";
              while (substr($zeile,-2,1)!='.')
              {
                //Dateiname auslesen
                if(preg_match('~mf:action~',$zeile))
                {
                  $temp = trim(str_replace('mf:action',"",$zeile));
                  $action = $testPath.trim(str_replace("<","",str_replace("> ;","",$temp)));
                }
                $zeile = fgets($manifest,4096);
              }
              //Hinzufügen zum Array
              $type = "negativeeval";
              $temparray = array($format,$type,$action,$result);
              array_push($testArray,$temparray);
            }*/
      //EVAL
            if(strpos($testType,"Eval")!= false)
            {
              //nachfolgende Zeilen Einlesen bis zum nächsten Punkt
              $zeile = fgets($manifest,4096);
              $action = "";
              $result = "";

              while (substr($zeile,-2,1)!='.')
              {
                //Dateiname auslesen
                if(preg_match('~mf:action~',$zeile))
                {
                  $temp = trim(str_replace("mf:action","",$zeile));
                  $action = $testPath.trim(str_replace("<","",str_replace("> ;","",$temp)));
                }
                //result auslesen
                else if(preg_match('~mf:result~',$zeile))
                {
                  $temp = trim(str_replace("mf:result","",$zeile));
                  $result = $testPath.trim(str_replace("<","",str_replace("> ;","",$temp)));
                }
                $zeile = fgets($manifest,4096);
              }
              //Hinzufügen zum Array
              $type = "eval";
              $temparray = array($format,$type,$action,$result);
              array_push($testArray,$temparray);
            }
      //POSTIVESYNTAX
            else if(strpos($testType,"PositiveSyntax")!= false)
            {
              //nachfolgende Zeilen Einlesen bis zum nächsten Punkt
              $zeile = fgets($manifest,4096);
              $action = "";
              $result = "";
              while (substr($zeile,-2,1)!='.')
              {
                //Dateiname auslesen
                if(preg_match('~mf:action~',$zeile))
                {
                  $temp = trim(str_replace("mf:action","",$zeile));
                  $action = $testPath.trim(str_replace("<","",str_replace("> ;","",$temp)));
                }
                $zeile = fgets($manifest,4096);
              }
              //Hinzufügen zum Array
              $type = "positivesyntax";
              $temparray = array($format,$type,$action,$result);
              array_push($testArray,$temparray);
            }
      /*//NEGATIVESYNTAX
            else if(strpos($testType,"NegativeSyntax")!= false)
            {
              //nachfolgende Zeilen Einlesen bis zum nächsten Punkt
              $zeile = fgets($manifest,4096);
              $action = "";
              $result = "";
              while (substr($zeile,-2,1)!='.')
              {
                //Dateiname auslesen
                if(preg_match('~mf:action~',$zeile))
                {
                  $temp = trim(str_replace("mf:action","",$zeile));
                  $action = $testPath.trim(str_replace("<","",str_replace("> ;","",$temp)));
                }
                $zeile = fgets($manifest,4096);
              }
              //Hinzufügen zum Array
              $type = "negativesyntax";
              $temparray = array($format,$type,$action,$result);
              array_push($testArray,$temparray);

            }*/
          }
      //SONDERFALL (XML Format oder Turtle am Ende)
      else
      {
      if(preg_match('~<#[a-zA-Z0-9_-]*?>~',$zeile))
      {
        $nextline = fgets($manifest,4096);
        //Fall XML
        if(preg_match('~<#[a-zA-Z0-9_-]*?> a rdft.*~',$zeile))
        {
          preg_match('~rdft:[a-zA-Z0-9]*~',$zeile,$match);
          $testType = trim(str_replace("rdft:","",$match[0]));

          //Auskommentierte XML Test ignorieren
          if(substr($zeile,0,1)=='#')
          {
          }
          else if(strpos($testType,"Eval")!= false)
          {
            $action = "";
            $result = "";
            while (true)
            {
              if(preg_match('~mf:action~',$nextline))
              {
                $temp = trim(str_replace("mf:action","",$nextline));
                $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~","",$temp));
                $action = $testPath.substr($action,0,-2);
              }
              else if(preg_match('~mf:result~',$nextline))
              {
                $temp = trim(str_replace("mf:result","",$nextline));
                $result = trim(preg_replace("~^[a-zA-Z0-9\.-_]~","",$temp));
                $result = $testPath.substr($result,0,-3);

                //Hinzufügen zum Array und Schleife verlassen
                $type = "eval";
                $temparray = array($format,$type,$action,$result);
                array_push($testArray,$temparray);
                break;
              }
              $nextline = fgets($manifest,4096);
            }
          }
          /*else if(strpos($testType,"NegativeSyntax")!= false)
          {
            $action = "";
            $result = "";
            $action = "";
            $result = "";
            while (true)
            {
              //Dateiname auslesen
              if(preg_match('~mf:action~',$nextline))
              {
                $temp = trim(str_replace("mf:action","",$nextline));
                $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~","",$temp));
                $action = $testPath.substr($action,0,-3);

                //Hinzuflügen zum Array und Schleife verlassen
                $type = "negativesyntax";
                $temparray = array($format,$type,$action,$result);
                array_push($testArray,$temparray);
                break;
              }
              else
              {
                $nextline = fgets($manifest,4096);
              }
            }
          }*/
        }
        //Fall Turtle
        else if(preg_match('~ .*?;~',$nextline))
        {
          //Auslesen des TestTypes
          preg_match('~rdft:[a-zA-Z0-9]*~',$nextline,$match);
          $testType = trim(str_replace("rdft:","",$match[0]));
          /*if(strpos($testType,"NegativeSyntax")!= false)
          {
            $action = "";
            $result = "";
            while (true)
            {
              //Dateiname auslesen
              if(preg_match('~mf:action~',$nextline))
              {
                $temp = trim(str_replace("mf:action","",$nextline));
                $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~","",$temp));
                $action = $testPath.substr($action,0,-3);

                //Hinzufügen zum Array und Schleife verlassen
                $type = "negativesyntax";
                $temparray = array($format,$type,$action,$result);
                array_push($testArray,$temparray);
                break;

              }
              else
              {
                $nextline = fgets($manifest,4096);
              }
            }
          }*/
          if(strpos($testType,"PositiveSyntax")!= false)
          {
            $action = "";
            $result = "";
            while (true)
            {
              //Dateiname auslesen
              if(preg_match('~mf:action~',$nextline))
              {
                $temp = trim(str_replace("mf:action","",$nextline));
                $action = trim(preg_replace("~^[a-zA-Z0-9\.-_]~","",$temp));
                $action = $testPath.substr($action,0,-3);

                //Hinzufügen zum Array und Schleife verlassen
                $type = "positivesyntax";
                $temparray = array($format,$type,$action,$result);
                array_push($testArray,$temparray);
                break;

                }
                else
                {
                  $nextline = fgets($manifest,4096);
                }
              }
          }
        }
      }
    }
  }
  return $testArray;
  }
}
