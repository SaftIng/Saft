<?php

namespace Saft\Cache\Adapter;

use Symfony\Component\Yaml\Parser;

class PHPArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Saves the cache type.
     *
     * @var string
     */
    protected $cacheType = 'phparray';

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
        $configFilepath = $saftRootDir . 'config.yml';

        // check for config file
        if (false === file_exists($configFilepath)) {
            throw new \Exception('config.yml missing in test/config.yml');
        }

        // parse YAML file
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configFilepath));
        
        $this->fixture = new PHPArray($this->config['phparrayCacheConfig']);
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

    public function testGetInvalidKey()
    {
        $this->assertNull($this->fixture->get(time().'invalid key'));
    }

    /**
     * function getType
     */

    public function testGetType()
    {
        $this->assertEquals($this->cacheType, $this->fixture->getType());
    }

    /**
     * function set
     */

    public function testSet()
    {
        $this->fixture->set('foo', 1);
        $this->assertEquals(1, $this->fixture->get('foo'));

        $this->fixture->set('foo', array(1));
        $this->assertEquals(array(1), $this->fixture->get('foo'));

        $this->fixture->set('foo', array(array(1)));
        $this->assertEquals(array(array(1)), $this->fixture->get('foo'));

        $this->fixture->set('foo', array(array('foo')));
        $this->assertEquals(array(array('foo')), $this->fixture->get('foo'));
    }
}
