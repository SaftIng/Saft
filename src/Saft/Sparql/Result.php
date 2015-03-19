<?php
namespace Saft\Sparql;

abstract class Result
{

    protected $resultObject;

    public function __construct($resultObject)
    {

    }

    public function isResultValue()
    {
        return $this->resultObject instanceof ResultValue;
    }

    public function isStatementIterator()
    {
        return $this->resultObject instanceof \Saft\Rdf\StatementIterator;
    }

    public function isResultIterator()
    {
        return $this->resultObject instanceof ResultIterator;
    }
}
