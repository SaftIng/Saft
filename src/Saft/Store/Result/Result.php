<?php
namespace Saft\Store\Result;

/**
 * This class represents the result of store operation, usually a SPARQL query.
 */
abstract class Result
{
    protected $resultObject;
    
    /**
     * @param mixed
     */
    public function __construct($resultObject = null)
    {
        $this->setResultObject($resultObject);
    }
    
    /**
     * 
     * @return mixed
     */
    public function getResultObject()
    {
        return $this->resultObject;
    }
    
    /**
     * @return boolean True, if this instance is an ExceptionResult
     */
    abstract public function isExceptionResult();
    
    /**
     * @return boolean True, if this instance is a SetResult
     */
    abstract public function isSetResult();
    
    /**
     * @return boolean True, if this instance is a StatementResult
     */
    abstract public function isStatementResult();
    
    /**
     * @return boolean True, if this instance is a ValueResult
     */
    abstract public function isValueResult();
    
    /**
     * @param mixed
     */
    public function setResultObject($resultObject)
    {
        $this->resultObject = $resultObject;
    }
}
