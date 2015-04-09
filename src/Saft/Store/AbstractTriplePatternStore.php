<?php

namespace Saft\Store;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\AbstractNamedNode;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\VariableImpl;
use \Saft\Sparql\Query;

/**
 * Predefined Pattern-statement Store. The Triple-methods need to be implemented in the specific statement-store.
 * The query method is defined in the abstract class and reroute to the triple-methods.
 */
abstract class AbstractTriplePatternStore implements StoreInterface
{

    /**
     * redirect to deleteMatchingStatement-methode
     * @param  string $query
     * @return answer from deleteMatchingStatement
     */
    public function delete($query)
    {
        //TODO
        $this->fixture = new Query();
        $this->fixture->init($query);
        /*$queryParts = $this->fixture->getQueryParts();
        print_r($queryParts);*/
        $queryParts = $this->fixture->getTriplePatterns();

        $statement = $this->getStatement($queryParts[0]);
        
        return $this->deleteMatchingStatements($statement);
    }

    /**
     * redirect to addStatements-methode
     * @param  string $query
     * @return answer from addStatements
     */
    public function add($query)
    {
        //TODO
        $this->fixture = new Query();
        $this->fixture->init($query);
        $queryParts = $this->fixture->getTriplePatterns();
        //print_r($queryParts);
        return $this->addStatements($this->getStatements($queryParts));
    }
    
    /**
     * redirect to getMatchingStatements-methode
     * @param  string $query
     * @return answer from getMatchingStatements
     */
    public function get($query)
    {
        //TODO
        $this->fixture = new Query();
        $this->fixture->init($query);
        $queryParts = $this->fixture->getTriplePatterns();

        $statement = $this->getStatement($queryParts[0]);
        return $this->getMatchingStatements($statement);
    }

    /**
     * redirect to hasMatchingStatement-methode
     * @param  string $query
     * @return answer from hasMatchingStatement
     */
    public function has($query)
    {
        //TODO
        $this->fixture = new Query();
        $this->fixture->init($query);
        $queryParts = $this->fixture->getTriplePatterns();

        $statement = $this->getStatement($queryParts[0]);
        return $this->hasMatchingStatement($statement);
    }
    
    /**
     * @param string $query   SPARQL query string.
     * @param string $options optional Further configurations.
     * @throws ?
     */
    public function query($query, array $options = array())
    {
        //@TODO
        if (stristr($query, 'select') || stristr($query, 'construct')) {
            $this->get($query);
        } elseif (strpos($query, 'delete')) {
            $this->delete($query);
        } elseif (stristr($query, 'insert')) {
            $this->add($query);
        }
    }

    /**
     * create Statement from query.
     * @param array $queryParts the part of the query with the description of the statement.
     * @return Statement           Statement-object
     */
    protected function getStatement(array $queryParts)
    {
        //print_r($queryParts);
        $subject = $this->createNode($queryParts['s'], $queryParts['s_type']);
        $predicate = $this->createNode($queryParts['p'], $queryParts['p_type']);
        $object = $this->createNode($queryParts['o'], $queryParts['o_type']);
        $statement = new StatementImpl($subject, $predicate, $object);

        return $statement;
    }

    /**
     * create statements from query
     * @param  array $queryParts the part of the query with the description of the statements.
     * @return StatementIterator             Statements
     */
    protected function getStatements(array $queryParts)
    {
        $statements = new ArrayStatementIteratorImpl(array());
        foreach ($queryParts as $st) {
            $statements->append($this->getStatement($st));
        }
        return $statements;
    }

    /**
     * Create a Node from string.
     *
     * @param  string $value value of Node
     * @return Node   Returns NamedNode, Variable or Literal
     */
    private function createNode($value, $type)
    {
        if ('uri' == $type) {
            return new NamedNodeImpl($value);
        } elseif ('var' == $type) {
            return new VariableImpl('?' . $value);
        } elseif ('typed-literal' == $type) {
            return new LiteralImpl($value);
        }
    }
}
