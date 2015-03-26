<?php
namespace Saft\Store;

use Saft\Cache\Cache;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementIterator;
use Saft\QueryCache\QueryCache;
use Saft\Backend\HttpStore\Store\Http;
use Saft\Backend\Virtuoso\Store\Virtuoso;

class StoreChain implements StoreInterface
{
    /**
     * Contains the chain of store instances.
     *
     * @var array of instances which implement Saft\Store\StoreInterface.
     */
    protected $chainEntries = array();
    
    /**
     * If set, all statement- and query related operations have to be in close collaboration with the
     * successor.
     *
     * @var StoreInterface
     */
    protected $successor;
    
    /**
     * redirects to the query method.
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator $statements          StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  string            $graphUri   optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array             $options    optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function addStatements(StatementIterator $statements, $graphUri = null, array $options = array())
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
            // just forward command to successor and return its result.
            return $this->successor->addStatements($statements, $graphUri, $options);
            
        // run command on chain entries
        } elseif (0 < count($this->getChainEntries())) {
            return $this->chainEntries[0]->addStatements($statements, $graphUri, $options);
        } else {
            throw new \Exception('No chain entries available and no successor set.');
        }
    }
    
    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
            // just forward command to successor and return its result.
            return $this->successor->deleteMatchingStatements($statement, $graphUri, $options);
            
        // run command on chain entries
        } elseif (0 < count($this->getChainEntries())) {
            return $this->chainEntries[0]->deleteMatchingStatements($statement, $graphUri, $options);
            
        } else {
            throw new \Exception('No chain entries available and no successor set.');
        }
    }
    
    /**
     * Returns array with graphUri's which are available.
     *
     * @return array Array which contains graph URI's as values and keys.
     */
    public function getAvailableGraphs()
    {
        // TODO switch to only call successor if chainEntries return empty result
        
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
            // just forward command to successor and return its result.
            return $this->successor->getAvailableGraphs();
            
        // run command on chain entries
        } else {
            return $this->chainEntries[0]->getAvailableGraphs();
        }
    }
    
    /**
     * Get saved chain entries.
     *
     * @return array Array which contains chain entries, which are instances which implement StoreInterface.
     */
    public function getChainEntries()
    {
        return $this->chainEntries;
    }
    
    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     */
    public function getMatchingStatements(Statement $statement, $graphUri = null, array $options = array())
    {
        $result = null;
        
        if (0 < count($this->getChainEntries())) {
            $result = $this->chainEntries[0]->getMatchingStatements($statement, $graphUri, $options);
        
            if (false === empty($result)) {
                return $result;
            }
        }
        
        // if successor is set, ask it if chain entries returned empty result.
        if (true === empty($result) && $this->successor instanceof StoreInterface) {
            // just forward command to successor and return its result.
            return $this->successor->getMatchingStatements($statement, $graphUri, $options);
            
        // both chain entries and, if available, the successor returned empty result.
        } else {
            return array();
        }
    }
    
    /**
     * @return array Empty array
     */
    public function getStoreDescription()
    {
        // if successor is set, ask it first before run the command yourself.
        if ($this->successor instanceof StoreInterface) {
            // just forward command to successor and return its result.
            return $this->successor->getStoreDescription();
            
        // run command on chain entries
        } elseif (0 < count($this->getChainEntries())) {
            return $this->chainEntries[0]->getStoreDescription();
            
        } else {
            throw new \Exception('No chain entries available and no successor set.');
        }
    }
    
    /**
     * redirects to the query method.
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  string    $graphUri  optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $statement, $graphUri = null, array $options = array())
    {
        $result = null;
        
        if (0 < count($this->getChainEntries())) {
            $result = $this->chainEntries[0]->hasMatchingStatement($statement, $graphUri, $options);
        
            // only return result if it is of type boolean
            if (true === is_bool($result)) {
                return $result;
            }
        }
        
        // if successor is set, ask it if chain entries returned empty result.
        if ((null === $result || true === empty($result)) && $this->successor instanceof StoreInterface) {
            // just forward command to successor and return its result.
            return $this->successor->hasMatchingStatement($statement, $graphUri, $options);
            
        }
        
        // if the function is here, no chain entries and successor are available.
        throw new \Exception('No chain entries available and no successor set.');
    }
    
    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query
     *                type, it returns either an instance of ResultIterator, StatementIterator, or ResultValue
     * @throws \Exception If query is no string.
     *                    If query is malformed.
     *                    If $options[resultType] = is neither extended nor array
     */
    public function query($query, array $options = array())
    {
        $result = $this->chainEntries[0]->query($query, $options);
        
        if (false === empty($result)) {
            return $result;
        
        // if successor is set, ask it if chain entries returned empty result.
        } elseif (true === empty($result) && $this->successor instanceof StoreInterface) {
            // just forward command to successor and return its result.
            return $this->successor->getAvailableGraphs();
            
        // both chain entries and, if available, the successor returned empty result.
        } else {
            return array();
        }
    }
    
    /**
     * Set successor instance. This method is useful, if you wanna build chain of instances which implement
     * StoreInterface. It sets another instance which will be later called, if a statement- or query-related
     * function gets called.
     * E.g. you chain a query cache and a virtuoso instance. In this example all queries will be handled by
     * the query cache first, but if no cache entry was found, the virtuoso instance gets called.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function setChainSuccessor(StoreInterface $successor)
    {
        $this->successor = $successor;
    }
    
    /**
     * Setup a chain of instances, which implement Saft\Store\StoreInterface.
     *
     * @param array $configuration Based on the this array stores will be setup by this instance. This array must
     *                             be 2-dimensional with first dimension contains arrays and the second dimension
     *                             are key-value pairs. E.g. array(array('type' => 'virtuoso', ...),...)
     * @throws \Exception If a requirement of a store instance is not fullfilled.
     * @throws \Exception If configuration is invalid.
     * @throws \Exception If field type is not set.
     * @throws \Exception If unknown type given.
     */
    public function setupChain(array $configuration)
    {
        if (true === is_array($configuration) && 0 < count($configuration)) {
            $this->chainEntries = array();
            $chainIndex = 0;
            
            /**
             * Basic structure of the $configuration array:
             *
             * array(
             *      array(
             *          type => 'virtuoso',
             *          username => 'dba',
             *          username => 'dba',
             *      ),
             *      array(
             *          ...
             *      ),
             *      ...
             * )
             */
            foreach ($configuration as $configEntry) {
                // setup entry
                $this->chainEntries[$chainIndex] = $this->setupChainEntry($configEntry);
                
                // set current entry as backend for the entry before
                if (0 < $chainIndex) {
                    $this->chainEntries[$chainIndex-1]->setChainSuccessor($this->chainEntries[$chainIndex]);
                }
                
                ++$chainIndex;
            }
        
        } else {
            throw new \Exception('Empty configuration array given.');
        }
    }
    
    /**
     * Setup a chain entry with a given configuration.
     *
     * @param  array                     $configEntry
     * @return Saft\Store\StoreInterface
     * @throws \Exception If a requirement of the store instance is not fullfilled.
     * @throws \Exception If configuration is invalid.
     * @throws \Exception If field type is not set.
     * @throws \Exception If unknown type given.
     */
    public function setupChainEntry(array $configEntry)
    {
        if (true === isset($configEntry['type'])) {
            $chainEntry = null;
            
            switch ($configEntry['type']) {
                case 'http':
                    $chainEntry = new Http($configEntry);
                    break;
                    
                case 'querycache':
                    // TODO change that, so that you only give a config array and QueryCache init cache by
                    //      itself
                    $cache = new Cache(array('type' => 'file'));
                    
                    $chainEntry = new QueryCache($cache);
                    break;
                    
                case 'virtuoso':
                    $chainEntry = new Virtuoso($configEntry);
                    break;
            
                default:
                    throw new \Exception('Unknown type given.');
                    break;
            }
            
            return $chainEntry;
            
        } else {
            throw new \Exception('Field "type" is not set.');
        }
    }
}
