<?php

namespace Saft\Skeleton\SparqlEndpoint;

use Negotiation\Negotiator;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactory;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\StatementIteratorFactory;
use Saft\Skeleton\Data\SerializerFactory;
use Saft\Sparql\Query\QueryUtils;
use Saft\Sparql\Result\Result;
use Saft\Sparql\Result\ResultFactory;
use Saft\Sparql\Result\SetResult;
use Saft\Sparql\Result\SetResultImpl;
use Saft\Sparql\Result\ValueResult;
use Saft\Sparql\Result\ValueResultImpl;
use Saft\Store\Store;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SparqlEndpoint
{
    /**
     * @var Negotiator
     */
    protected $negotiator;

    /**
     * @var NodeUtils
     */
    protected $nodeUtils;

    /**
     * @var QueryUtils
     */
    protected $queryUtils;

    /**
     * @var SerializerFactory
     */
    protected $serializerFactory;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @param Store $store
     * @param SerializerFactory $serializerFactory
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     * @param StatementIteratorFactory $statementIteratorFactory
     * @param NodeUtils $nodeUtils
     * @param QueryUtils $queryUtils
     * @param Negotiator $negotiator
     */
    public function __construct(
        Store $store,
        SerializerFactory $serializerFactory,
        NodeFactory $nodeFactory,
        StatementFactory $statementFactory,
        StatementIteratorFactory $statementIteratorFactory,
        ResultFactory $resultFactory,
        NodeUtils $nodeUtils,
        QueryUtils $queryUtils,
        Negotiator $negotiator
    ) {
        $this->negotiator = $negotiator;
        $this->nodeUtils = $nodeUtils;
        $this->nodeFactory = $nodeFactory;
        $this->queryUtils = $queryUtils;
        $this->resultFactory = $resultFactory;
        $this->serializerFactory = $serializerFactory;
        $this->statementFactory = $statementFactory;
        $this->statementIteratorFactory = $statementIteratorFactory;
        $this->store = $store;
    }

    /**
     * @param string $mediaType
     */
    public function getAccordingSerializer($mediaType)
    {
        /*
         * RDF/XML
         */
        $map['application/rdf+xml'] = 'rdf-xml';
        $map['application/xhtml+xml'] = 'rdf-xml';
        $map['application/xml'] = 'rdf-xml';
        $map['text/xml'] = 'rdf-xml';

        /*
         * N3
         */
        $map['text/n3'] = 'n-triples';
        $map['text/rdf+n3'] = 'n-triples';

        /*
         * Turtle
         */
        $map['application/x-turtle'] = 'turtle';
        $map['text/turtle'] = 'turtle';

        if (isset($map[$mediaType])) {
            return $this->serializerFactory->createSerializerFor($map[$mediaType]);
        } else {
            throw new Exception('Invalid mediaType given: '. $mediaType);
        }
    }

    /**
     * @param Request $request
     * @throws \Exception if given media type (accept header) is not valid.
     */
    public function handleRequest(Request $request)
    {
        $headers = array();

        try {
            /*
             * check accept header
             */
            // what media types we do support
            $serverPriorities = array('application/x-turtle');
            $mediaType = $this->negotiator->getBest($request->headers->get('accept'), $serverPriorities);
            if (null !== $mediaType) {
                $headers['Content-Type'] = $mediaType->getValue();
            } else {
                // no accept header given, use default: application/sparql-results+xml
                // https://www.w3.org/TR/sparql11-protocol/#query-bindings-http-examples
                $headers['Content-Type'] = 'application/x-turtle';
            }
            $serializer = $this->getAccordingSerializer($headers['Content-Type']);

            // no query parameter given
            if ('GET' == $request->getMethod() && null == $request->query->get('query')) {
                throw new \Exception(
                    Response::$statusTexts[500],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );

            } elseif ('GET' == $request->getMethod() && '' == $request->query->get('query')) {
                // TODO implement service description

            } elseif ('GET' == $request->getMethod()) {
                $query = urldecode($request->query->get('query'));
            }

            $rawResult = $this->store->query($query);

            $result = $this->transformResultToResultSet($rawResult, $query);

            $statusCode = 200;

            // transform iterator to a string and write it to temp file
            // afterwards read content and save it into $content
            // TODO find a more performant solution
            $filepath = tempnam(sys_get_temp_dir(), 'saft_');
            $testFile = fopen('file://' . $filepath, 'w+');

            $serializer->serializeIteratorToStream($result, $testFile);

            $content = trim(file_get_contents($filepath));
            fclose($testFile);

        } catch (\Exception $e) {
            // https://www.w3.org/TR/sparql11-protocol/#query-failure
            // TODO distinguish between "SPARQL update request string is not a legal sequence of characters"
            //      and "if the service fails to execute the update request"
            $statusCode = Response::HTTP_OK < $e->getCode() && $e->getCode() < 600
                ? $e->getCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            $content = $e->getMessage();
        }

        return new Response($content, $statusCode, $headers);
    }

    /**
     * @param Result|boolean $result
     */
    public function transformResultToResultSet($rawResult, $usedQuery)
    {
        $queryType = $this->queryUtils->getQueryType($usedQuery);

        if ('selectQuery' == $queryType) {
            return $this->transformSetResultToResultSet($rawResult);
        } elseif ('constructQuery' == $queryType) {
            return $this->transformStatementResultSetToStatementIterator($rawResult);
        } elseif ('askQuery' == $queryType) {
            return $this->transformValueResultToResultSet($rawResult);
        } else {
            throw new \Exception('TODO: implement non-select and non-construct query types');
        }
    }

    /**
     * @param SetResult|StatementSetResult|StatementIterator $result
     */
    public function transformSetResultToResultSet($_result)
    {
        // transform StatementSetResult to SetResult instance
        if ($_result instanceof SetResult && true == $_result->isStatementSetResult()) {
            $result = $this->transformStatementSetResultToSetResult($_result);
        } else if ($_result instanceof SetResult) {
            $result = $_result;
        } else {
            throw new \Exception('Unknown result type given: ' . get_class($_result));
        }

        /*
            turtle representation looks like:

            @prefix res: <http://www.w3.org/2005/sparql-results#> .
            @prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
            @prefix rdfdf:	<http://www.openlinksw.com/virtrdf-data-formats#> .
            _:_ a res:ResultSet .
            _:_ res:resultVariable "s" , "p" .
            _:_ res:solution [
                  res:binding [ res:variable "s" ; res:value rdfdf:default-iid ] ;
                  res:binding [ res:variable "p" ; res:value rdf:type ]
            ] .
            _:_ res:solution [
                  res:binding [ res:variable "s" ; res:value rdfdf:default-iid-nullable ] ;
                  res:binding [ res:variable "p" ; res:value rdf:type ]
            ] .

         */
        $statements = array();
        $resultSetNode = $this->nodeFactory->createBlankNode('ResultSet');

        $statements[] = $this->statementFactory->createStatement(
            $resultSetNode,
            $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
            $this->nodeFactory->createNamedNode('http://www.w3.org/2005/sparql-results#ResultSet')
        );

        // add variables
        foreach ($result->getVariables() as $variable) {
            $statements[] = $this->statementFactory->createStatement(
                $resultSetNode,
                $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#resultVariable'),
                $this->nodeFactory->createLiteral($variable)
            );
        }

        /*
            add set entries. example:

            _:_ res:solution [
                res:binding [ res:variable "s" ; res:value rdfdf:default-iid ] ;
                res:binding [ res:variable "p" ; res:value rdf:type ]
            ] .
         */
        $solutionId = 0;
        $bindingId = 0;
        foreach ($result as $setEntry) {
            // _:_ res:solution [
            $solutionBlankNode = $this->nodeFactory->createBlankNode('solution'. $solutionId);
            $statements[] = $this->statementFactory->createStatement(
                $resultSetNode,
                $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#solution'),
                $solutionBlankNode
            );

            // fill solution blank node
            foreach ($result->getVariables() as $variable) {
                $bindingBlankNode = $this->nodeFactory->createBlankNode('binding'. $bindingId++);

                // _:solution1 res:binding _:binding1
                $statements[] = $this->statementFactory->createStatement(
                    $solutionBlankNode,
                    $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#binding'),
                    $bindingBlankNode
                );

                // res:variable "s" ;
                $statements[] = $this->statementFactory->createStatement(
                    $bindingBlankNode,
                    $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#variable'),
                    $this->nodeFactory->createLiteral($variable)
                );

                // res:value "my-value"
                $statements[] = $this->statementFactory->createStatement(
                    $bindingBlankNode,
                    $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#value'),
                    $setEntry[$variable]
                );
            }

            ++$solutionId;
        }

        return $this->statementIteratorFactory->createStatementIteratorFromArray($statements);
    }

    /**
     * @param StatementIterator $result
     * @return SetResult
     */
    public function transformStatementIteratorToSetResult(StatementIterator $statementIterator)
    {
        $setEntries = array();

        foreach ($statementIterator as $statement) {
            $setEntries[] = array(
                's' => $statement->getSubject(),
                'p' => $statement->getPredicate(),
                'o' => $statement->getObject()
            );
        }

        $result = $this->resultFactory->createSetResult($setEntries);
        $result->setVariables(array('s', 'p', 'o'));

        return $result;
    }

    /**
     * @param SetResult Instance of SetResult which is similar or equal to StatementSetResult.
     * @return SetResult
     */
    public function transformStatementSetResultToSetResult(SetResult $statementSetResult)
    {
        if ($statementSetResult->isSetResult()) {
            return $statementSetResult;
        }

        $setEntries = array();

        foreach ($statementSetResult as $statement) {
            $setEntries[] = array(
                's' => $statement->getSubject(),
                'p' => $statement->getPredicate(),
                'o' => $statement->getObject()
            );
        }

        $result = $this->resultFactory->createSetResult($setEntries);
        $result->setVariables(array('s', 'p', 'o'));
        return $result;
    }

    /**
     * @param SetResult Instance of SetResult which is similar or equal to StatementSetResult.
     * @return SetResult
     */
    public function transformStatementResultSetToStatementIterator(SetResult $statementSetResult)
    {
        if (false == $statementSetResult->isStatementSetResult()) {
            throw new \Exception('Given instance is not of type SetResult and no StatementResultSet.');
        }

        $statements = array();

        foreach ($statementSetResult as $statement) {
            $statements[] = $statement;
        }

        return $this->statementIteratorFactory->createStatementIteratorFromArray($statements);
    }

    /**
     * @param ValueResult $result
     * @return StatementIterator
     */
    public function transformValueResultToResultSet($result)
    {
        $statements = array();
        $resultSetNode = $this->nodeFactory->createBlankNode('ResultSet');

        // [] rdf:type rs:results ;
        $statements[] = $this->statementFactory->createStatement(
            $resultSetNode,
            $this->nodeFactory->createNamedNode('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
            $this->nodeFactory->createNamedNode('http://www.w3.org/2005/sparql-results#results')
        );

        // .. ; rs:boolean true .
        // assuming that only boolean as value are possible
        $statements[] = $this->statementFactory->createStatement(
            $resultSetNode,
            $this->nodeFactory->createNamedNode('http://www.w3.org/2005/sparql-results#boolean'),
            $this->nodeFactory->createLiteral(
                true === $result ? 'true' : 'false',
                $this->nodeFactory->createNamedNode('http://www.w3.org/2001/XMLSchema#boolean')
            )
        );

        return $this->statementIteratorFactory->createStatementIteratorFromArray($statements);
    }
}
