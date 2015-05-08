<?php

namespace Saft\Cache\Test;

use Saft\TestCase;
use Saft\Cache\Cache;
use Saft\Cache\CacheFactoryImpl;
use Symfony\Component\Yaml\Parser;

abstract class AbstractCacheTest extends TestCase
{

    /**
     * Contains an instance of the class to test.
     *
     * @var mixed
     */
    protected $fixture;

    /**
     *
     */
    public function setUp()
    {
        // set path to test dir
        $saftRootDir = dirname(__FILE__) . '/../../../../';
        $configFilepath = $saftRootDir . 'test-config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('test-config.yml missing');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));
    }

    /**
     *
     */
    public function tearDown()
    {
        if (null !== $this->fixture) {
            $this->fixture->clean();
        }

        parent::tearDown();
    }

    /**
     * function checkRequirements
     */

    public function testCheckRequirements()
    {
        $this->assertTrue($this->fixture->checkRequirements());
    }

    /**
     * function clean
     */

    public function testClean()
    {
        $this->assertNull($this->fixture->get('foo'));
        $this->fixture->set('foo', 'bar');
        $this->assertEquals('bar', $this->fixture->get('foo'));

        $this->fixture->clean();

        $this->assertNull($this->fixture->get('foo'));
    }

    /**
     * function delete
     */

    public function testDelete()
    {
        $this->assertNull($this->fixture->get('foo'));

        $this->fixture->set('foo', 'bar');

        $this->assertEquals('bar', $this->fixture->get('foo'));

        $this->fixture->delete('foo');

        $this->assertNull($this->fixture->get('foo'));
    }

    /**
     * function get
     */

    public function testGet()
    {
        $this->assertNull($this->fixture->get('foo'));

        $this->fixture->set('foo', 'bar');

        $this->assertEquals('bar', $this->fixture->get('foo'));
    }

    /**
     * Tests getCacheObject
     */

    public function testGetCacheObject()
    {
        $this->assertTrue($this->fixture instanceof Cache);
    }

    /**
     * tests getCompleteEntry
     */

    public function testGetCompleteEntry()
    {
        $this->fixture->set('foo', 'bar');

        $this->assertEquals(
            array(
                'get_count' => 1,
                'set_count' => 1,
                'value' => 'bar'
            ),
            $this->fixture->getCompleteEntry('foo')
        );
    }

    public function testGetCompleteEntryNoEntry()
    {
        $this->assertNull($this->fixture->getCompleteEntry('foo'));
    }

    public function testGetCompleteEntryCallMultipleTimes()
    {
        $value = array('foo' => new \stdClass());

        $this->fixture->set('foo', $value);

        $this->assertEquals(
            array('get_count' => 1, 'set_count' => 1, 'value' => $value),
            $this->fixture->getCompleteEntry('foo')
        );

        $this->assertEquals(
            array('get_count' => 2, 'set_count' => 1, 'value' => $value),
            $this->fixture->getCompleteEntry('foo')
        );

        $this->assertEquals(
            array('get_count' => 3, 'set_count' => 1, 'value' => $value),
            $this->fixture->getCompleteEntry('foo')
        );
    }

    public function testGetCompleteEntryMultipleAccessesBefore()
    {
        $this->fixture->set('foo', 'bar');

        // accessed 3 times
        $this->fixture->get('foo');
        $this->fixture->get('foo');
        $this->fixture->get('foo');

        $this->assertEquals(
            array(
                'get_count' => 4,
                'set_count' => 1,
                'value' => 'bar'
            ),
            $this->fixture->getCompleteEntry('foo')
        );
    }

    public function testGetInvalidKey()
    {
        $this->assertNull($this->fixture->get(time().'invalid key'));
    }

    /**
     * function constructor
     */

    public function testInitInvalidClass()
    {
        // expect exception, because an invalid class was given
        $this->setExpectedException('\Exception');

        $cacheFactory = new CacheFactoryImpl();
        $this->fixture = $cacheFactory->createCache(array('class' => time()));
    }

    public function testInitNoClass()
    {
        // expect exception, because no class was given
        $this->setExpectedException('\Exception');

        $cacheFactory = new CacheFactoryImpl();
        $this->fixture = $cacheFactory->createCache(array());
    }

    /**
     * function set
     */

    public function testSetDifferentKeyTypes()
    {
        // no special chars
        $this->fixture->set('testSet_normal', 1);
        $this->assertEquals(1, $this->fixture->get('testSet_normal'));

        // special chars
        $this->fixture->set('testSet_üöäß', 1);
        $this->assertEquals(1, $this->fixture->get('testSet_üöäß'));
    }

    public function testSetDifferentValueTypes()
    {
        // int
        $this->fixture->set('testSet_int', 1);
        $this->assertEquals(1, $this->fixture->get('testSet_int'));

        // one dimensional array
        $this->fixture->set('testSet_1dimarray', array(1));
        $this->assertEquals(array(1), $this->fixture->get('testSet_1dimarray'));

        // multi dimensional array
        $this->fixture->set('testSet_multidimarray', array(array('foo')));
        $this->assertEquals(array(array('foo')), $this->fixture->get('testSet_multidimarray'));

        // object instance
        $this->fixture->set('testSet_object', new \stdClass());
        $this->assertEquals(new \stdClass(), $this->fixture->get('testSet_object'));

        // object instance in array
        $this->fixture->set('testSet_object', array('foo' => new \stdClass()));
        $this->assertEquals(array('foo' => new \stdClass()), $this->fixture->get('testSet_object'));
    }
}
