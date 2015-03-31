<?php
namespace Saft\Store\Test\Result;

use Saft\Rdf\StatementImpl;
use Saft\Rdf\VariableImpl;
use Saft\Store\Result\StatementResult;

class StatementResultUnitTest extends \PHPUnit_Framework_TestCase
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
        parent::setUp();
        
        $this->fixture = new StatementResult();
    }
    
    public function testConstructorNonIteratorInstance()
    {
        $this->setExpectedException('Exception');
        
        $this->fixture = new StatementResult('invalid');
    }
    
    /**
     * Tests append
     */
    
    public function testAppend()
    {
        $statement = new StatementImpl(new VariableImpl(), new VariableImpl(), new VariableImpl());

        $this->fixture->append($statement);
    }
    
    public function testAppendInvalidStatement()
    {
        $this->setExpectedException('Exception');
        
        $this->fixture->append(null);
    }
    
    /**
     * Tests that class exists
     */
    public function testExistense()
    {
        $this->assertTrue(class_exists('\Saft\Store\Result\StatementResult'));
    }
    
    /**
     * Tests isExceptionResult
     */
    
    public function testIsExceptionResult()
    {
        $this->assertFalse($this->fixture->isExceptionResult());
    }
    
    /**
     * Tests isSetResult
     */
    
    public function testIsSetResult()
    {
        $this->assertFalse($this->fixture->isSetResult());
    }
    
    /**
     * Tests isStatementResult
     */
    
    public function testIsStatementResult()
    {
        $this->assertTrue($this->fixture->isStatementResult());
    }
    
    /**
     * Tests isValueResult
     */
    
    public function testIsValueResult()
    {
        $this->assertFalse($this->fixture->isValueResult());
    }
}
