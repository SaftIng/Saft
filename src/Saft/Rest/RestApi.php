<?php
namespace Saft\Rest;

use Saft\Store\StoreInterface;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\NamedNode;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementImpl;

/**
 * http://coreymaynard.com/blog/creating-a-restful-api-with-php/
 */
class RestApi extends \Saft\Rest\RestAbstract
{
    public function __construct($request, $origin, StoreInterface $store)
    {
        parent::__construct($request, $store);
    }

    /**
     * Rest-Endpoint
     * @return [type] [description]
     */
    protected function store()
    {
        if ($this->verb == "statements") {
            if (!isset($_POST['statementsarray'])) {
                throw new \Exception('no statements passed.');
            }
            $statementsPost = $_POST['statementsarray'];
            foreach ($statementsPost as $st) {
                if (is_array($st)) {
                    if (sizeof($st) == 3) {
                        $st[3] = null;
                    } elseif (sizeof($st) != 4) {
                        throw new \Exception('wrong statements-format. not a triple an not a quad');
                    }
                } else {
                    if (sizeof($statementsPost) == 3) {
                        $statementsPost[3] = null;
                    } elseif (sizeof($statementsPost) != 4) {
                        throw new \Exception('wrong statements-format. not a triple an not a quad');
                    }
                }
            }

            //TODO eliminate redundancy
            
            //AddStatements
            if ($this->method == 'POST') {
                $statements = array();
                $i = 0;
                foreach ($statementsPost as $st) {
                    $statement = $this->createStatement($st[0], $st[1], $st[2], $st[3]);
                    $statements[$i] = $statement;
                    $i++;
                }
                $statements = new ArrayStatementIteratorImpl($statements);
                return $this->store->addStatements($statements);

            //deleteMatchingStatements
            } elseif ($this->method == 'DELETE') {
                if (is_array($statementsPost[0])) {
                    throw new \Exception('expect just one statement');
                }
                $statement = $this->createStatement(
                    $statementsPost[0],
                    $statementsPost[1],
                    $statementsPost[2],
                    $statementsPost[3]
                );
                return $this->store->deleteMatchingStatements($statement);

            //getMatchingStatements
            } elseif ($this->method == 'GET') {
                if (is_array($statementsPost[0])) {
                    throw new \Exception('expect just one statement');
                }
                $statement = $this->createStatement(
                    $statementsPost[0],
                    $statementsPost[1],
                    $statementsPost[2],
                    $statementsPost[3]
                );
                return $this->store->getMatchingStatements($statement);

            } else {
                return "Only accepts POST/GET/DELETE requests";
            }
        } if ($this->verb == "store") {
            if ($this->method == 'GET') {
                //get Graphs
            }
        } else {
            return "Wrong input";
        }
    }

    private function createStatement($sub, $pred, $obj, $gr = null)
    {
        $subject = $this->createNode($sub);
        $predicate = $this->createNode($pred);
        $object = $this->createNode($obj);
        $graph = $this->createNode($gr);

        $statement = new StatementImpl($subject, $predicate, $object, $graph);
        return $statement;
    }

    private function validLiteral()
    {

    }

    private function createNode($value)
    {
        //TODO triple-pattern
        if (true === NamedNode::check($value)
            || null === $value) {
            return new NamedNode($value);
        } else {
            throw new \Exception('atleast one Parameter is not a valid URI.');
        }
    }
}
