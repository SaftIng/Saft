<?php

namespace Saft\Store;

use Saft\Backend\HttpStore\Store\Http;
use Saft\Backend\Virtuoso\Store\Virtuoso;
use Saft\Cache\Cache;
use Saft\QueryCache\QueryCache;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIterator;
use Saft\Store\Result\Result;
use Saft\Store\Result\EmptyResult;

class StoreChain implements Store
{
    /**
     * Contains the chain of store instances.
     *
     * @var array of instances which implement Store
     */
    protected $chainEntries = array();

    /**
     * redirects to the query method.
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator $statements          StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  Node              $graph      optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array             $options    optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function addStatements(StatementIterator $statements, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // run command on chain entries
        if (true === isset($this->chainEntries[0])) {
            return $this->chainEntries[0]->addStatements($statements, $graph, $options);

        // dont run the command by yourself
        } else {
            throw new \Exception('No chain entries available and no successor set.');
        }
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // run command on chain entries
        if (true === isset($this->chainEntries[0])) {
            return $this->chainEntries[0]->deleteMatchingStatements($statement, $graph, $options);

        // dont run the command by yourself
        } else {
            throw new \Exception('No chain entries available, cant run command by myself.');
        }
    }

    /**
     * Returns array with graphUri's which are available.
     *
     * @return array Array which contains graph URI's as values and keys.
     */
    public function getAvailableGraphs()
    {
        // run command on chain entries
        if (true === isset($this->chainEntries[0])) {
            return $this->chainEntries[0]->getAvailableGraphs();

        // dont run the command by yourself
        } else {
            throw new \Exception('No chain entries available, cant run command by myself.');
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
     * @param  Node      $graph     optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // run command on chain entries
        if (true === isset($this->chainEntries[0])) {
            return $this->chainEntries[0]->getMatchingStatements($statement, $graph, $options);
        }

        // dont run the command by yourself
        throw new \Exception('No chain entries available, cant run command by myself.');
    }

    /**
     * @return array Empty array
     */
    public function getStoreDescription()
    {
        // run command on chain entries
        if (true === isset($this->chainEntries[0])) {
            return $this->chainEntries[0]->getStoreDescription();
        }

        // dont run the command by yourself
        throw new \Exception('No chain entries available, cant run command by myself.');
    }

    /**
     * redirects to the query method.
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param  Statement $statement          It can be either a concrete or pattern-statement.
     * @param  Node      $graph     optional Overrides target graph.
     * @param  array     $options   optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO migrate code to new interface
        $graphUri = null;
        if ($graph !== null) {
            $graphUri = $graph->getUri();
        }

        // run command on chain entries
        if (true === isset($this->chainEntries[0])) {
            return $this->chainEntries[0]->hasMatchingStatement($statement, $graph, $options);
        }

        // dont run the command by yourself
        throw new \Exception('No chain entries available, cant run command by myself.');
    }

    /**
     * This method sends a SPARQL query to the store.
     *
     * @param  string $query            The SPARQL query to send to the store.
     * @param  array  $options optional It contains key-value pairs and should provide additional
     *                                  introductions for the store and/or its adapter(s).
     * @return Result Returns result of the query. Depending on the query type, it returns either an instance
     *                of EmptyResult, SetResult, StatementResult or ValueResult.
     * @throws \Exception If query is no string.
     * @throws \Exception If query is malformed.
     */
    public function query($query, array $options = array())
    {
        // run command on chain entries
        if (true === isset($this->chainEntries[0])) {
            return $this->chainEntries[0]->query($query, $options);
        }

        // dont run the command by yourself
        throw new \Exception('No chain entries available, cant run command by myself.');
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
     * TODO make it more dynamic, include class to load into given $configuration
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
                    $chainEntry = new Http(new NodeFactoryImpl(), new StatementFactoryImpl(), $configEntry);
                    break;

                case 'querycache':
                    // TODO change that, so that you only give a config array and QueryCache init cache by
                    //      itself
                    $cache = new Cache(array('type' => 'file'));

                    $chainEntry = new QueryCache($cache);
                    break;

                case 'virtuoso':
                    $chainEntry = new Virtuoso(new NodeFactoryImpl(), new StatementFactoryImpl(), $configEntry);
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

    /**
     * Create a new graph with the URI given as Node. If the underlying store implementation doesn't support empty
     * graphs this method will have no effect.
     *
     * @param Node $graph The graph name used for the newly created graph
     * @param array $options optional additional key-value pairs passed to the store implementation
     *
     * @throws \Exception If the given graph could not be created
     */
    public function createGraph(NamedNode $graph, array $options = array())
    {
        // TODO: Implement createGraph() method.
    }

    /**
     * Removes the given graph from the store.
     *
     * @param Node $graph The name of the graph to drop
     * @param array $options optional additional key-value pairs passed to the store implementation
     *
     * @throws \Exception If the given graph could not be droped
     */
    public function dropGraph(NamedNode $graph, array $options = array())
    {
        // TODO: Implement dropGraph() method.
    }
}
