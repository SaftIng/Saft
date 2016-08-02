<?php

namespace Saft\Addition\Rest;

use Saft\Rdf\NodeUtils;
use Saft\Store\Store;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

/**
 *
 */
class Hub
{
    /**
     * @var Store
     */
    protected $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store, NodeUtils $nodeUtils)
    {
        $this->nodeUtils = $nodeUtils;
        $this->store = $store;
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
                && ('*' != $serverParams[$param]
                && false == $this->nodeUtils->simpleCheckURI($serverParams[$param]))) {
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

            return new Response('php://memory', 200);

        /*
         * invalid request given.
         */
        } else {
            $response = new Response('php://memory', $result['code']);
            $response->getBody()->write($result['message']);

            return $response;
        }
    }
}
