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
        //TODO eliminate redundancy
        if ($this->verb == "statements") {
            if ($this->method == 'POST') {
                $statementsPost = $_POST['statements'];
                $statements = array();
                $i = 0;
                foreach ($statementsPost as $st) {
                    $statement = $this->createStatement($st[0], $st[1], $st[2], $st[3]);
                    $statements[$i] = $statement;
                    $i++;
                }
                $statements = new ArrayStatementIteratorImpl($statements);
                return $this->store->addStatements($statements);
            } elseif ($this->method == 'DELETE') {
                $sub = $_POST['subject'];
                $pred = $_POST['predicate'];
                $obj = $_POST['object'];
                $gr = $_POST['graph'];

                $statement = $this->createStatement($sub, $pred, $obj, $gr);
                return $this->store->deleteMatchingStatements($statement);

            } elseif ($this->method == 'GET') {
                $sub = $_POST['subject'];
                $pred = $_POST['predicate'];
                $obj = $_POST['object'];
                $gr = $_POST['graph'];

                $statement = $this->createStatement($sub, $pred, $obj, $gr);
                return $this->store->getMatchingStatements($statement);

            } else {
                return "Only accepts POST/get/delete requests";
            }
        } if ($this->verb == "store") {
            if ($this->method == 'GET') {
                //get Graphs
            }
        } else {
            return "Wrong input";
        }
    }

    private function createStatement($sub, $pred, $obj, $gr)
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
        if (true === NamedNode::check($value)
            || null === $value
            || true === NamedNode::isVariable($value)) {
            return new NamedNode($value);
        } else {
            throw new \Exception('atleast one Parameter is not a valid URI.');
        }
    }
}
