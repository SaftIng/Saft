<?php

namespace Saft\Store;

class DispenseableResourceTest extends \Saft\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->store->addGraph($this->testGraphUri);

        // init instance of DispenseableResource
        $this->fixture = new \Saft\Store\DispenseableResource(
            $this->store->getGraph($this->testGraphUri)
        );

        // remove test namespace from global namespace registry of EasyRdf\RdfNamespace
        \EasyRdf\RdfNamespace::delete("enable");
    }

    /**
     * function __get
     */

    public function testGetIsSetNot()
    {
        $this->fixture->init();

        $this->assertFalse(
            isset($this->fixture->foo)
        );
    }

    public function testGetValue()
    {
        $this->assertFalse(
            isset($this->fixture->foo)
        );

        $this->fixture->init();

        $this->assertFalse(
            isset($this->fixture->foo)
        );

        $this->fixture->foo = "0";

        $this->assertEquals(
            $this->fixture->foo,
            "0"
        );
    }

    /**
     * function __set
     */

    public function testGet()
    {
        $this->fixture->init();

        $this->fixture->foo = 0;
    }

    /**
     * function addNamespace
     */

    public function testAddNamespace()
    {
        $this->fixture->addNamespace("enable", "http://enable/");

        $this->assertEquals(
            "http://enable/",
            \EasyRdf\RdfNamespace::get("enable")
        );
    }

    /**
     * function addNamespace
     */

    public function testDeleteNamespace()
    {
        $this->fixture->addNamespace("enable", "http://enable/");

        $this->assertEquals(
            "http://enable/",
            \EasyRdf\RdfNamespace::get("enable")
        );

        $this->fixture->deleteNamespace("enable");

        $this->assertNull(
            \EasyRdf\RdfNamespace::get("enable")
        );
    }

    /**
     * function generateTriples
     */

    public function testGenerateTriples()
    {
        $this->fixture->init();

        $this->fixture->foo = "bar";
        $this->fixture->foo1 = "bar-1";

        $this->assertEquals(
            array(
                array(
                    $this->fixture->uri,
                    "a",
                    array(
                        "type" => "uri",
                        "value" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                    )
                ),
                array(
                    $this->fixture->uri,
                    $this->fixture->uri . urlencode("foo"),
                    array(
                        "type" => "literal",
                        "value" => "bar"
                    )
                ),
                array(
                    $this->fixture->uri,
                    $this->fixture->uri . urlencode("foo1"),
                    array(
                        "type" => "literal",
                        "value" => "bar-1"
                    )
                ),
            ),
            $this->fixture->generateTriples()
        );
    }

    /**
     * function load
     */

    public function testLoad()
    {
        $this->fixture->init();

        $this->fixture->foo = "bar";

        $this->fixture->save();

        $this->fixture->load($this->fixture->id);

        $this->assertEquals(
            "bar",
            $this->fixture->foo
        );
    }

    public function testLoadNamespace()
    {
        $this->fixture->init();

        $this->fixture->addNamespace("enable", "http://enable/");

        $this->fixture->{"enable:ttt"} = "bar";

        $this->fixture->save();

        $this->fixture->load($this->fixture->id);

        $this->assertEquals(
            "bar",
            $this->fixture->{"enable:ttt"}
        );
    }

    public function testLoadUnknownNamespace()
    {
        $this->setExpectedException("\Exception");

        $this->fixture->init();

        $this->fixture->{"enable:hihi"} = "bar";

        $this->fixture->save();
    }

    /**
     * function init
     */

    public function testInit()
    {
        $this->assertFalse(
            isset($this->fixture->id)
        );

        $this->fixture->init();

        $this->assertTrue(
            isset($this->fixture->id)
        );
    }

    /**
     * function save
     */

    public function testSaveSimple()
    {
        $this->store->clearGraph($this->testGraphUri);

        $this->fixture->init();

        $this->fixture->foo = "bar";

        $this->fixture->save();

        $result = $this->store->getGraph($this->testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );

        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->fixture->uri,
                    "p" => $this->fixture->uri . "foo",
                    "o" => "bar"
                ),
            ),
            $result
        );
    }

    public function testSaveWithNamespaces()
    {
        $this->store->clearGraph($this->testGraphUri);

        $this->fixture->init();

        $this->fixture->{"rdf:subject"} = "bar";

        $this->fixture->save();

        $result = $this->store->getGraph($this->testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );

        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->fixture->uri,
                    "p" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#subject",
                    "o" => "bar"
                ),
            ),
            $result
        );
    }

    public function testSaveWithOverriding()
    {
        $this->store->clearGraph($this->testGraphUri);

        $this->fixture->init();

        $this->fixture->foo = "bar";

        $this->fixture->save();

        /**
         * SPARQL for the first change
         */

        $result = $this->store->getGraph($this->testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );

        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->fixture->uri,
                    "p" => $this->fixture->uri . "foo",
                    "o" => "bar"
                ),
            ),
            $result
        );

        $this->fixture->ttt = "foobar";

        $this->fixture->save();

        /**
         * SPARQL for the second change
         */
        $result2 = $this->store->getGraph($this->testGraphUri)->sparql(
            "SELECT ?s ?p ?o WHERE {?s ?p ?o.};"
        );

        $this->assertEqualsArrays(
            array(
                array(
                    "s" => $this->fixture->uri,
                    "p" => "a",
                    "o" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
                ),
                array(
                    "s" => $this->fixture->uri,
                    "p" => $this->fixture->uri . "foo",
                    "o" => "bar"
                ),
                array(
                    "s" => $this->fixture->uri,
                    "p" => $this->fixture->uri . "ttt",
                    "o" => "foobar"
                ),
            ),
            $result2
        );
    }
}
