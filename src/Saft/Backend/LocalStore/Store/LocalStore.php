<?php

namespace Saft\Backend\LocalStore\Store;

use Saft\Store\StoreInterface;
use Saft\Store\AbstractTriplePatternStore;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\Statement;

/**
 * Simple file based store working in a single directory. A .store file in the
 * directory is used to hold the meta deta.
 */
class LocalStore extends AbstractTriplePatternStore
{
    /**
     * {@inheritdoc}
     */
    public function getAvailableGraphs()
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function addStatements(StatementIterator $statements, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingStatements(Statement $Statement, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatchingStatement(Statement $Statement, $graphUri = null, array $options = array())
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreDescription()
    {
        throw new \Exception('Unsupported Operation');
    }

    /**
     * {@inheritdoc}
     */
    public function setChainSuccessor(StoreInterface $successor)
    {
        throw new \Exception('Unsupported Operation');
    }
}
