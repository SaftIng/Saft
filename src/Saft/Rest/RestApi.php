<?php
namespace Saft\Rest;

use Saft\Store\StoreInterface;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\StatementImpl;

/**
 * @todo  hasMatchingStatement missing
 */
class RestApi extends AbstractRest
{
    public function __construct($request, $origin, StoreInterface $store)
    {
        parent::__construct($request, $store);
    }

    /**
     * Rest-Endpoint
     * servername/store/statements
     * * POST for add statements
     * * DELETE for delete matching statement
     * * GET for get matching statement
     * servername/store/graph
     * * GET for get available Graphs
     * @return mixed
     */
    protected function store()
    {
        //servername/store/statements
        if ($this->verb == "statements") {
            if (!isset($_POST['statementsarray'])) {
                throw new \Exception('no statements passed.');
            }
            $statements = $_POST['statementsarray'];
            foreach ($statements as $st) {
                if (!is_array($st)) {
                    if (sizeof($statements) == 3) {
                        $statements[3] = null;
                    } elseif (sizeof($statements) != 4) {
                        throw new \Exception('wrong statements-format. not a triple an not a quad');
                    }
                }
            }
            // set graphUri if given
            $graphUri = null;
            if (isset($_POST['graphUri'])) {
                if (NamedNodeImpl::check($_POST['graphUri'])
                    || '?' == substr($_POST['graphUri'], 0, 1)
                ) {
                    $graphUri = $_POST['graphUri'];
                } else {
                    throw new \Exception('graphUri not valid.');
                }
            }
            
            //AddStatements
            if ($this->method == 'POST') {
                $array = array();
                $i = 0;
                foreach ($statements as $st) {
                    if (sizeof($st) == 3) {
                        $statement = $this->createStatement($st[0], $st[1], $st[2]);
                    } elseif (sizeof($st) == 4) {
                        $statement = $this->createStatement($st[0], $st[1], $st[2], $st[3]);
                    } else {
                        throw new \Exception('wrong statements-format. not a triple an not a quad');
                    }
                    $array[$i] = $statement;
                    $i++;
                }
                $stArray = new ArrayStatementIteratorImpl($array);
                return $this->store->addStatements($stArray, $graphUri);

            } else {
                if (is_array($statements[0])) {
                    throw new \Exception('expect just one statement');
                }
                //deleteMatchingStatements
                if ($this->method == 'DELETE') {
                    $statement = $this->createStatement(
                        $statements[0],
                        $statements[1],
                        $statements[2],
                        $statements[3]
                    );
                    return $this->store->deleteMatchingStatements($statement, $graphUri);

                    //getMatchingStatements
                } elseif ($this->method == 'GET') {
                    $statement = $this->createStatement(
                        $statements[0],
                        $statements[1],
                        $statements[2],
                        $statements[3]
                    );
                    return $this->store->getMatchingStatements($statement, $graphUri);

                } else {
                    return "Only accepts POST/GET/DELETE requests";
                }
            }
            //servername/store/graph
        } elseif ($this->verb == "graph") {
            if ($this->method == 'GET') {
                return $this->store->getAvailableGraphs();
            } else {
                return "Only accepts POST requests";
            }
        } else {
            return "Wrong input";
        }
    }

    /**
     * Create a Statement from strings.
     * @param  string $sub
     * @param  string $pred
     * @param  string $obj
     * @param  string $gr
     * @return Statement
     */
    private function createStatement($sub, $pred, $obj, $gr = null)
    {
        $subject = $this->createNode($sub);
        $predicate = $this->createNode($pred);
        $object = $this->createNode($obj);
        $graph = $this->createNode($gr);

        $statement = new StatementImpl($subject, $predicate, $object, $graph);
        return $statement;
    }

    /**
     * Create a Node from string.
     * @param  string $value value of Node
     * @return Node        return NamedNode, Variable oder Literal
     */
    private function createNode($value)
    {
        if (true === NamedNodeImpl::check($value)
            || null === $value
        ) {
            return new NamedNodeImpl($value);
        } elseif ('?' == substr($value, 0, 1)) {
            return new VariableImpl($value);
        } else {
            return new LiteralImpl($value);
        }
    }
}
