<?php 

namespace Saft;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function setUp()
    {
        $this->_fixture = new \Saft\Cache(array(
            "type" => "phparray"
        ));
    }
    
    /**
     * instanciation 
     */
     
    public function testInstanciation()
    {
        $this->_fixture = new \Saft\Cache(array(
            "type" => "phparray"
        ));
    }
    
    /**
     * function clean 
     */
     
    public function testClean()
    {
        $this->assertFalse($this->_fixture->get("foo"));
        $this->_fixture->set("foo", "bar");        
        $this->assertEquals("bar", $this->_fixture->get("foo"));
        
        $this->_fixture->clean();
        
        $this->assertFalse($this->_fixture->get("foo"));
    }
    
    /**
     * function delete 
     */
     
    public function testDelete()
    {
        $this->assertFalse(
            $this->_fixture->get("foo")
        );
        
        $this->_fixture->set("foo", "bar");
        
        $this->assertEquals(
            "bar", $this->_fixture->get("foo")
        );
        
        $this->_fixture->delete("foo");
        
        $this->assertFalse(
            $this->_fixture->get("foo")
        );
    }
    
    /**
     * function get 
     */
     
    public function testGet()
    {
        $this->assertFalse(
            $this->_fixture->get("foo")
        );
        
        $this->_fixture->set("foo", "bar");
        
        $this->assertEquals(
            "bar", $this->_fixture->get("foo")
        );
    }
     
    public function testGet_invalidKey()
    {
        $this->assertFalse($this->_fixture->get(time()."invalid key"));
    }
    
    /**
     * function getType
     */
     
    public function testGetType()
    {
        $this->assertEquals("phparray", $this->_fixture->getType());
    }
     
    /**
     * function init
     */
     
    public function testInit()
    {
        $this->_fixture->init(array(
            "type" => "phparray"
        ));
    }
     
    public function testInit_invalidType()
    {
        $this->setExpectedException("\Exception");
        
        $this->_fixture->init(array("type" => "invalidType"));
    }
    
    /**
     * function set
     */
     
    public function testSet()
    {
        $this->_fixture->set("foo", 1);
        $this->assertEquals(1, $this->_fixture->get("foo"));
        
        $this->_fixture->set("foo", array(1));
        $this->assertEquals(array(1), $this->_fixture->get("foo"));
        
        $this->_fixture->set("foo", array(array(1)));
        $this->assertEquals(array(array(1)), $this->_fixture->get("foo"));
        
        $this->_fixture->set("foo", array(array("foo")));
        $this->assertEquals(array(array("foo")), $this->_fixture->get("foo"));
    }    
}
