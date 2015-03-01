<?php

namespace Saft;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function setUp()
    {
        $this->fixture = new \Saft\Cache(array(
            "type" => "phparray"
        ));
    }

    /**
     * instanciation
     */

    public function testInstanciation()
    {
        $this->fixture = new \Saft\Cache(array(
            "type" => "phparray"
        ));
    }

    /**
     * function clean
     */

    public function testClean()
    {
        $this->assertFalse($this->fixture->get("foo"));
        $this->fixture->set("foo", "bar");
        $this->assertEquals("bar", $this->fixture->get("foo"));

        $this->fixture->clean();

        $this->assertFalse($this->fixture->get("foo"));
    }

    /**
     * function delete
     */

    public function testDelete()
    {
        $this->assertFalse(
            $this->fixture->get("foo")
        );

        $this->fixture->set("foo", "bar");

        $this->assertEquals(
            "bar",
            $this->fixture->get("foo")
        );

        $this->fixture->delete("foo");

        $this->assertFalse(
            $this->fixture->get("foo")
        );
    }

    /**
     * function get
     */

    public function testGet()
    {
        $this->assertFalse(
            $this->fixture->get("foo")
        );

        $this->fixture->set("foo", "bar");

        $this->assertEquals(
            "bar",
            $this->fixture->get("foo")
        );
    }

    public function testGetInvalidKey()
    {
        $this->assertFalse($this->fixture->get(time()."invalid key"));
    }

    /**
     * function getType
     */

    public function testGetType()
    {
        $this->assertEquals("phparray", $this->fixture->getType());
    }

    /**
     * function init
     */

    public function testInit()
    {
        $this->fixture->init(array(
            "type" => "phparray"
        ));
    }

    public function testInitInvalidType()
    {
        $this->setExpectedException("\Exception");

        $this->fixture->init(array("type" => "invalidType"));
    }

    /**
     * function set
     */

    public function testSet()
    {
        $this->fixture->set("foo", 1);
        $this->assertEquals(1, $this->fixture->get("foo"));

        $this->fixture->set("foo", array(1));
        $this->assertEquals(array(1), $this->fixture->get("foo"));

        $this->fixture->set("foo", array(array(1)));
        $this->assertEquals(array(array(1)), $this->fixture->get("foo"));

        $this->fixture->set("foo", array(array("foo")));
        $this->assertEquals(array(array("foo")), $this->fixture->get("foo"));
    }
}
