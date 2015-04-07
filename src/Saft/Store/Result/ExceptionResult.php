<?php
namespace Saft\Store\Result;

class ExceptionResult extends Result
{
    /**
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        parent::__construct($exception);
    }
    
    /**
     * @return boolean False
     */
    public function isEmptyResult()
    {
        return false;
    }
    
    /**
     * @return boolean True
     */
    public function isExceptionResult()
    {
        return true;
    }
    
    /**
     * @return boolean False
     */
    public function isSetResult()
    {
        return false;
    }
    
    /**
     * @return boolean False
     */
    public function isStatementResult()
    {
        return false;
    }
    
    /**
     * @return boolean False
     */
    public function isValueResult()
    {
        return false;
    }
}
