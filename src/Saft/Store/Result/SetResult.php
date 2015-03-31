<?php
namespace Saft\Store\Result;

/**
 * This class represents a result set.
 */
class SetResult extends Result implements \Iterator
{
    /**
     * Constructor.
     *
     * @param mixed $resultObject optional Must be null or an instance of a class which implements \Iterator.
     */
    public function __construct($resultObject = null)
    {
        $this->setResultObject($resultObject);
    }
    
    /**
     * @param mixed $entry
     */
    public function append($entry)
    {
        $this->resultObject->append($entry);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->resultObject->current();
    }
    
    /**
     * @return boolean False
     */
    public function isExceptionResult()
    {
        return false;
    }
    
    /**
     * @return boolean True
     */
    public function isSetResult()
    {
        return true;
    }
    
    /**
     * @return boolean False
     */
    public function isStatementResult()
    {
        return false;
    }
    
    /**
     * @return boolean True
     */
    public function isValueResult()
    {
        return false;
    }

    /**
     * @return int position in the statements array
     */
    public function key()
    {
        return $this->resultObject->key();
    }

    /**
     * Any returned value is ignored.
     * @return void
     */
    public function next()
    {
        $this->resultObject->next();
    }
    
    /**
     * @param mixed
     */
    public function setResultObject($resultObject)
    {
        if (null !== $resultObject && $resultObject instanceof \Iterator) {
            parent::setResultObject($resultObject);
            
        } elseif (null === $resultObject) {
            // that means that the result will be filled with data later on. we only have to init an instance
            // of a class which implements the Iterator interface.
            parent::__construct(new \ArrayIterator());
            
        } else {
            throw new \Exception('Parameter $resultObject must implement Iterator interface.');
        }
    }

    /**
     * Implement rewind, because we can
     */
    public function rewind()
    {
        $this->resultObject->rewind();
    }
    
    /**
     * @return boolean
     */
    public function valid()
    {
        return $this->resultObject->valid();
    }
}
