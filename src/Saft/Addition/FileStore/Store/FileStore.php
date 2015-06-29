<?php
namespace Saft\Addition\FileStore\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Store\AbstractTriplePatternStore;

/**
 * Class FileStore
 * @package Saft\Addition\FileStore\Store
 */
class FileStore extends AbstractTriplePatternStore
{
    /**
     * @var The path to the store directory
     */
    protected $directoryPath;

    /**
     * @var The directory index containing filenames as key and graph URIs as value
     */
    protected $directoryIndex = null;

    /**
     * @param $directoryPath string The path to the directory where the models are managed
     */
    public function __construct($directoryPath, NodeFactory $nodeFactory, StatementFactory $statementFactory)
    {
        $this->directoryPath = $directoryPath;

        parent::__construct($nodeFactory, $statementFactory);
    }

    /**
     * Returns a list of all available graph URIs of the store.
     * It can also respect access control, to only returned available graphs in the current context.
     *
     * @return array Simple array of graph URIs.
     */
    public function getGraphs()
    {
        $index = $this->getDirectoryIndex();
        return array_values($index);
    }

    /**
     * Adds multiple Statements to (default-) graph.
     *
     * @param  StatementIterator|array $statements StatementList instance must contain Statement instances
     *                                                which are 'concret-' and not 'pattern'-statements.
     * @param  Node $graph optional Overrides target graph. If set, all statements will
     *                                                be add to that graph, if available.
     * @param  array $options optional It contains key-value pairs and should provide additional
     *                                                introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case an error occur, an exception
     *                 will be thrown.
     */
    public function addStatements($statements, Node $graph = null, array $options = array())
    {
        // TODO: Implement addStatements() method.
    }

    /**
     * Removes all statements from a (default-) graph which match with given statement.
     *
     * @param  Statement $Statement It can be either a concrete or pattern-statement.
     * @param  Node $graph optional Overrides target graph. If set, all statements will be delete in
     *                                       that graph.
     * @param  array $options optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true, if function performed without errors. In case
     *                 an error occur, an exception will be thrown.
     */
    public function deleteMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO: Implement deleteMatchingStatements() method.
    }

    /**
     * It gets all statements of a given graph which match the following conditions:
     * - statement's subject is either equal to the subject of the same statement of the graph or it is null.
     * - statement's predicate is either equal to the predicate of the same statement of the graph or it is null.
     * - statement's object is either equal to the object of a statement of the graph or it is null.
     *
     * @param  Statement $Statement It can be either a concrete or pattern-statement.
     * @param  Node $graph optional Overrides target graph. If set, you will get all
     *                                       matching statements of that graph.
     * @param  array $options optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return StatementIterator It contains Statement instances  of all matching statements of the given graph.
     */
    public function getMatchingStatements(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO: Implement getMatchingStatements() method.
        return new ArrayStatementIteratorImpl([]);
    }

    /**
     * Returns true or false depending on whether or not the statements pattern
     * has any matches in the given graph.
     *
     * @param  Statement $Statement It can be either a concrete or pattern-statement.
     * @param  Node $graph optional Overrides target graph.
     * @param  array $options optional It contains key-value pairs and should provide additional
     *                                       introductions for the store and/or its adapter(s).
     * @return boolean Returns true if at least one match was found, false otherwise.
     */
    public function hasMatchingStatement(Statement $statement, Node $graph = null, array $options = array())
    {
        // TODO: Implement hasMatchingStatement() method.
    }

    /**
     * Get information about the store and its features.
     *
     * @return array Array which contains information about the store and its features.
     */
    public function getStoreDescription()
    {
        // TODO: Implement getStoreDescription() method.
    }

    protected function getDirectoryIndex()
    {
        if ($this->directoryIndex === null) {
            if ($handle = opendir($this->directoryPath)) {
                while (false !== ($entry = readdir($handle))) {
                    if (preg_match('/^(.*)\.graph$/', $entry, $matches) == 1) {
                        if ($fp = fopen($entry, 'r')) {
                            $uri = trim(fgets($fp));
                            $this->directoryIndex[$matches[1]] = $uri;
                            fclose($fp);
                        }
                    }
                }
                closedir($handle);
            }
        }

        return $this->directoryIndex;
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
