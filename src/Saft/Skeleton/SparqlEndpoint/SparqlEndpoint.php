<?php

namespace Saft\Skeleton\SparqlEndpoint;

use Negotiation\Negotiator;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIterator;
use Saft\Skeleton\Data\SerializerFactory;
use Saft\Sparql\Query\QueryUtils;
use Saft\Sparql\Result\SetResultImpl;
use Saft\Store\Store;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SparqlEndpoint
{
    /**
     * @var QueryUtils
     */
    protected $queryUtils;

    /**
     * @var SerializerFactory
     */
    protected $serializerFactory;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store, SerializerFactory $serializerFactory, QueryUtils $queryUtils)
    {
        $this->queryUtils = $queryUtils;
        $this->serializerFactory = $serializerFactory;
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
            $negotiator = new Negotiator();
            $serverPriorities = array('application/x-turtle');
            $mediaType = $negotiator->getBest($request->headers->get('accept'), $serverPriorities);
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

            $result = $this->store->query($query);
            $queryType = $this->queryUtils->getQueryType($query);

            if ('selectQuery' == $queryType || 'constructQuery' == $queryType) {
                $result = $this->transformSetResultToResultSet($result);
            } else {
                throw new \Exception('TODO: implement non-select and non-construct query types');
            }

            $statusCode = 200;

            $serializer->serializeIteratorToStream($result, 'php://memory');

            $content = file_get_contents('php://memory');

        } catch (\Exception $e) {
            // https://www.w3.org/TR/sparql11-protocol/#query-failure
            // TODO distinguish between "SPARQL update request string is not a legal sequence of characters"
            //      and "if the service fails to execute the update request"
            $statusCode = Response::HTTP_OK < $e->getCode()
                ? $e->getCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            $content = $e->getMessage();
        }

        return new Response($content, $statusCode, $headers);
    }

    /**
     * @param StatementIterator $result
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

        $result = new SetResultImpl($setEntries);
        $result->setVariables(array('s', 'p', 'o'));

        return $result;
    }

    /**
     * @param SetResult|StatementSetResult|StatementIterator $result
     */
    public function transformSetResultToResultSet($_result)
    {
        // transform StatementSetResult to SetResult instance
        if ($_result instanceof StatementSetResult && $_result->isStatementSetResult()) {
            $result = $_result->toSetResult();
        } else if ($_result instanceof StatementIterator) {
            $result = $this->transformStatementIteratorToSetResult($_result);
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
        $resultSetNode = new BlankNodeImpl('ResultSet' . rand(0, 1000));

        $statements[] = new StatementImpl(
            $resultSetNode,
            new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
            new NamedNodeImpl('http://www.w3.org/2005/sparql-results#ResultSet')
        );

        // add variables
        foreach ($result->getVariables() as $variable) {
            $statements[] = new StatementImpl(
                $resultSetNode,
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#resultVariable'),
                new LiteralImpl($variable)
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
            $solutionBlankNode = new BlankNodeImpl('solution'. $solutionId);
            $statements[] = new StatementImpl(
                $resultSetNode,
                new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#solution'),
                $solutionBlankNode
            );

            // fill solution blank node
            foreach ($result->getVariables() as $variable) {
                $bindingBlankNode = new BlankNodeImpl('binding'. $bindingId++);

                // _:solution1 res:binding _:binding1
                $statements[] = new StatementImpl(
                    $solutionBlankNode,
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#binding'),
                    $bindingBlankNode
                );

                // res:variable "s" ;
                $statements[] = new StatementImpl(
                    $bindingBlankNode,
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#variable'),
                    new LiteralImpl($variable)
                );

                // res:value "my-value"
                $statements[] = new StatementImpl(
                    $bindingBlankNode,
                    new NamedNodeImpl('http://www.w3.org/1999/02/22-rdf-syntax-ns#value'),
                    $setEntry[$variable]
                );
            }

            ++$solutionId;
        }

        return new ArrayStatementIteratorImpl($statements);
    }
}
