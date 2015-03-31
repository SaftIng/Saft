<?php
namespace Saft\Store\Test\Result;

use Saft\Store\Result\Result;

class ResultUnitTest extends \PHPUnit_Framework_TestCase
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
        $this->fixture = $this->getMockBuilder('Saft\Store\Result\Result')->getMock();
    }
    
    /**
     * Tests getResultObject
     */
     
    public function testGetResultObject()
    {
        $this->assertEquals(
            null,
            $this->fixture->getResultObject()
        );
    }
}
