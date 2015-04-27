<?php

namespace Saft\Cache\Test;

use Saft\Cache\Cache;
use Saft\Cache\CacheInterface;
use Saft\TestCase;
use Symfony\Component\Yaml\Parser;

class CacheUnitTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        $config = array('type' => 'phparray');
        
        $this->fixture = new Cache($config);
    }

    /**
     * Tests init
     */
     
    public function testInitKeyPrefixSet()
    {
        // throws exception, because no type was given
        $this->fixture->init(array('type' => 'phparray', 'keyPrefix' => 'foo'));
    }
     
    public function testInitNoTypeSet()
    {
        $this->setExpectedException('\Exception');
        
        // throws exception, because no type was given
        $this->fixture->init(array());
    }
     
    public function testInitUnknownTypeSet()
    {
        $this->setExpectedException('\Exception');
        
        // throws exception, because no type was given
        $this->fixture->init(array('type' => 'unknown type'));
    }

    /**
     * Tests isSerialized
     */
     
    public function testIsSerializedBooleanFalse()
    {
        $this->assertTrue(Cache::isSerialized('b:0;'));
    }
    
    public function testIsSerializedInvalidNull()
    {
        // N;
        
        // false, because second character is not ;
        $this->assertFalse(Cache::isSerialized('N_'));
    }
    
    public function testIsSerializedInvalidObjectMissingColon()
    {
        // O:strlen(object name):object name:object size:{s:strlen(property name):property name:property
        // definition;(repeated per property)}
        
        // false, because second character is no :
        $this->assertFalse(Cache::isSerialized('O_no_colon'));
    }
    
    public function testIsSerializedInvalidObjectNoStrlen()
    {
        // O:strlen(object name):object name:object size:{s:strlen(property name):property name:property
        // definition;(repeated per property)}
        
        // false, because third character is not a number
        $this->assertFalse(Cache::isSerialized('O: '));
    }
    
    public function testIsSerializedInvalidString()
    {
        // serialized form a string: s:size:value;
        // example of serialized string: s:3:"foo";
        
        // false, because last quotation mark is missing
        $this->assertFalse(Cache::isSerialized('s:3:"foo;'));
    }
    
    public function testIsSerializedInvalidType()
    {
        // false, because unknown first character
        $this->assertFalse(Cache::isSerialized('unknown_first_character'));
    }
     
    public function testIsSerializedNoString()
    {
        $this->assertFalse(Cache::isSerialized(42));
    }
     
    public function testIsSerializedValidBoolean()
    {
        $this->assertTrue(Cache::isSerialized('b:0;'));
        $this->assertTrue(Cache::isSerialized('b:1;'));
    }
     
    public function testIsSerializedValidNull()
    {
        $this->assertTrue(Cache::isSerialized('N;'));
    }
     
    public function testIsSerializedValidString()
    {
        // serialized form a string: s:size:value;
        // example of serialized string: s:3:"foo";
        $this->assertTrue(Cache::isSerialized('s:3:"foo";'));
        $this->assertTrue(Cache::isSerialized('s:0:"";'));
    }
}
