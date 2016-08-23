<?php

namespace Saft\Skeleton\Rest;

use Saft\Data\SerializationUtils;
use Saft\Data\Serializer;
use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactory;
use Saft\Sparql\Result\SetResult;
use Saft\Store\Store;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 *
 */
class Hub
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var NodeUtils
     */
    protected $nodeUtils;

    /**
     * @var array
     */
    protected $preallocateParameters;

    /**
     * @var Serializer
     */
    protected $serializer;

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
     * @param StatementFactory $statementFactory
     * @param NodeFactory $nodeFactory
     * @param Serializer $serializer
     * @param NodeUtils $nodeUtils
     */
    public function __construct(
        Store $store,
        StatementFactory $statementFactory,
        NodeFactory $nodeFactory,
        Serializer $serializer,
        NodeUtils $nodeUtils
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->serializer = $serializer;
        $this->statementFactory = $statementFactory;
        $this->store = $store;
        $this->nodeUtils = $nodeUtils;
    }

    /**
     * Validate given request. Specification is located under:
     * http://safting.github.io/doc/restinterface/triplestore
     *
     * @param RequestInterface $request
     * @return array|true Returns true, if request is valid, otherwise an array with keys 'message' and 'key' set.
     */
    protected function checkRequest(RequestInterface $request)
    {
        /*
         * Check method
         */
        if ('GET' != $request->getMethod() && 'POST' != $request->getMethod()) {
            // invalid method
            return array('message' => 'php://temp', 'code' => 405);
        }

        /*
         * Check HTTP headers
         */
        $headers = $request->getHeaders();

        /*
         * check Accept
         */
        if (isset($headers['Accept']) && is_array($headers['Accept'])) {
            // no elements means error
            if (0 == count($headers['Accept'])) {
                return array(
                    'message' => 'Bad Request: Accept headers can not be empty.',
                    'code' => 400
                );
            }

            // first entry must be a string
            if (isset($headers['Accept'][0]) && false == is_string($headers['Accept'][0])) {
                return array(
                    'message' => 'Bad Request: Accept headers must be a string.',
                    'code' => 400
                );
            }
        }

        /*
         * Check parameter
         */
        $serverParams = $request->getServerParams();

        /*
         * check s, p and o
         */
        foreach (array('s', 'p', 'o') as $param) {
            // check if s or p or o is set
            if (false == isset($serverParams[$param])) {
                return array(
                    'message' => 'Bad Request: Parameter '. $param .' not set.',
                    'code' => 400
                );
            }

            // s or p or o must be * or an URI
            if (isset($serverParams[$param])
                && ('*' != $serverParams[$param] && false == $this->nodeUtils->simpleCheckURI($serverParams[$param]))) {
                return array(
                    'message' => 'Bad Request: Parameter '. $param .' is invalid. Must be * or an URI.',
                    'code' => 400
                );
            }
        }

        /*
         * if o is set and not *, ot is mandatory (must be set to uri or literal)
         */
        // check that ot is set
        if (true == $this->nodeUtils->simpleCheckURI($serverParams['o'])
            && false === isset($serverParams['ot'])) {
            return array(
                'message' => 'Bad Request: Parameter o is an URI, so ot must be set.',
                'code' => 400
            );
        }

        // check that ot is either uri or literal
        if (true == $this->nodeUtils->simpleCheckURI($serverParams['o'])
            && false == in_array($serverParams['ot'], array('literal', 'uri'))) {
            return array(
                'message' => 'Bad Request: Parameter ot is neither uri nor literal.',
                'code' => 400
            );
        }

        /*
         * check optional parameters
         */

        /*
         * action - possible verbs: add, ask, count, delete, get
         */
        if (isset($serverParams['action'])) {
            // check for possible verbs
            if (false == in_array($serverParams['action'], array('add', 'ask', 'count', 'delete', 'get'), true)) {
                return array(
                    'message' =>
                        'Bad Request: Parameter action must be one of these verbs: add, ask, count, delete, get',
                    'code' => 400
                );
            }
        }

        /*
         * case_insensitive - possible values are true or false
         */
        if (isset($serverParams['case_insensitive'])) {
            // check for possible verbs
            if (false == in_array($serverParams['case_insensitive'], array('true', 'false', true, false), true)) {
                return array(
                    'message' =>
                        'Bad Request: Parameter case_insensitive must be one of these verbs: true, false',
                    'code' => 400
                );
            }
        }

        /*
         * graphUri - URI of the graph to execute the query on
         */
        if (isset($serverParams['graphUri'])) {
            // check for possible verbs
            if (false == $this->nodeUtils->simpleCheckURI($serverParams['graphUri'])) {
                return array(
                    'message' => 'Bad Request: Parameter graphUri must be an URI.',
                    'code' => 400
                );
            }
        }

        /*
         * limit - must be an integer equal or higher than 0
         */
        if (isset($serverParams['limit'])) {
            // limit must be an integer
            if (false == ctype_digit(ltrim((string)$serverParams['limit'], '-'))) {
                return array(
                    'message' => 'Bad Request: Parameter limit is not an integer.',
                    'code' => 400
                );
            }

            // limit must be equal or higher than 0
            if (0 > (int)$serverParams['limit']) {
                return array(
                    'message' => 'Bad Request: Parameter limit is not equal or higher than 0.',
                    'code' => 400
                );
            }
        }

        /*
         * offset - must be an integer equal or higher than 0
         */
        if (isset($serverParams['offset'])) {
            // offset must be an integer
            if (false == ctype_digit(ltrim((string)$serverParams['offset'], '-'))) {
                return array(
                    'message' => 'Bad Request: Parameter offset is not an integer.',
                    'code' => 400
                );
            }

            // offset must be equal or higher than 0
            if (1 > (int)$serverParams['offset']) {
                return array(
                    'message' => 'Bad Request: Parameter offset is not equal or higher than 1.',
                    'code' => 400
                );
            }
        }

        /*
         * reasoning_on - possible values are true or false (as string or boolean)
         */
        if (isset($serverParams['reasoning_on'])) {
            // check for possible verbs
            if (false == in_array($serverParams['reasoning_on'], array('true', 'false', true, false), true)) {
                return array(
                    'message' =>
                        'Bad Request: Parameter reasoning_on must be one of these verbs: true, false',
                    'code' => 400
                );
            }
        }

        return true;
    }

    /**
     * This function will compute a given request object and returns the response to return. To be
     * compatible with different implementations, the given $request parameter must implement
     * RequestInterface from PSR-7.
     *
     * @param RequestInterface $request
     * @return ResponseInterface Instance of ResponseInterface which represents the response to return.
     */
    public function computeRequest(RequestInterface $request)
    {
        /*
         * Validate given request
         */
        if (true === ($result = $this->checkRequest($request))) {
            // if we reach that point, the given $request was considered valid.

            // sets unset parameters with default values
            $this->preallocateParameters($request);

            switch ($this->preallocateParameters['action']) {
                case 'get':
                    return $this->computeGetRequest($request);

            }

            $response = new Response('php://memory', 500);
            $response->getBody()->write('Unknown error.');
            return $response;

        /*
         * invalid request given.
         */
        } else {
            $response = new Response('php://memory', $result['code']);
            $response->getBody()->write($result['message']);

            return $response;
        }
    }

    /**
     * This function computes a request in which the action was set to get.
     *
     * @param RequestInterface $request
     * @return ResponseInterface Instance of ResponseInterface which represents the response to return.
     */
    protected function computeGetRequest(RequestInterface $request)
    {
        /*
         * Build statement
         */
        // subject
        if ('*' == $this->preallocateParameters['s']) {
            $s = $this->nodeFactory->createAnyPattern();
        } else {
            $s = $this->nodeFactory->createNamedNode($this->preallocateParameters['s']);
        }
        // predicate
        if ('*' == $this->preallocateParameters['p']) {
            $p = $this->nodeFactory->createAnyPattern();
        } else {
            $p = $this->nodeFactory->createNamedNode($this->preallocateParameters['p']);
        }
        // object
        if ('*' == $this->preallocateParameters['o']) {
            $o = $this->nodeFactory->createAnyPattern();
        } else {
            $o = $this->nodeFactory->createNamedNode($this->preallocateParameters['o']);
        }
        // graph
        if ('*' == $this->preallocateParameters['graphUri']) {
            $g = $this->nodeFactory->createAnyPattern();
        } else {
            $g = $this->nodeFactory->createNamedNode($this->preallocateParameters['graphUri']);
        }

        $statement = $this->statementFactory->createStatement($s, $p, $o, $g);

        // execute get query on the store
        $resultStatements = $this->store->getMatchingStatements($statement);

        // get headers
        $headers = $this->getHeaders($request);

        $serializationUtils = new SerializationUtils();

        $tempFile = fopen('file://' . tempnam(sys_get_temp_dir(), 'saft_'), 'w+');

        // TODO implement usage of q values
        // https://github.com/SaftIng/Saft/issues/48
        if (isset($headers['Accept'][0])) {
            $serialization = $serializationUtils->mimeToSerialization($headers['Accept'][0]);
        } else {
            // TODO implement fallback based on the capabilities of the serializer instance
            $serialization = 'n-quads';
        }

        //
        $this->serializer->serializeIteratorToStream(
            $this->transformStatementSetResultToStatementIterator($resultStatements),
            // create temporary file to write result to
            $tempFile,
            $serialization
        );

        // set reference to file to response object and return it
        $stream = new Stream($tempFile);
        $stream->rewind();

        return new Response($stream);
    }

    /**
     * Simplifies and extend given HTTP headers for easier usage.
     *
     * @param RequestInterface $request
     */
    protected function getHeaders(RequestInterface $request)
    {
        $givenHeaders = $request->getHeaders();
        $adaptedHeaders = array();

        /*
         * Accept
         */
        if (isset($givenHeaders['Accept'])) {
            $adaptedHeaders['Accept'] = explode(',', $givenHeaders['Accept'][0]);
        }

        return $adaptedHeaders;
    }

    /**
     * Sets unset parameters with default values.
     *
     * @param RequestInterface $request
     */
    protected function preallocateParameters(RequestInterface $request)
    {
        $this->preallocateParameters = array();

        // copy server parameter and use it as preallocated parameters base
        // FYI: we checked the request before, so there should be no harmful parameters.
        $this->preallocateParameters = $request->getServerParams();

        // action
        if (false === isset($this->preallocateParameters['action'])) {
            $this->preallocateParameters['action'] = 'get';
        }

        // graphUri
        if (false === isset($this->preallocateParameters['graphUri'])) {
            $this->preallocateParameters['graphUri'] = '*';
        }
    }

    /**
     * @param SetResult Instance of SetResult, which contains only instances of Statement. $iterator
     * @return StatementIterator
     * @todo transform according code part to a more clear solution to avoid obsolete transformations
     */
    protected function transformStatementSetResultToStatementIterator(SetResult $result)
    {
        if (false == $result->isStatementSetResult()) {
            throw new Exception('Instance of SetResult is not a StatementSetResult.');
        }

        $statements = array();

        foreach ($result as $statement) {
            $statements[] = $statement;
        }

        return new ArrayStatementIteratorImpl($statements);
    }
}
