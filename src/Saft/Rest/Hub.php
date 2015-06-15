<?php

namespace Saft\Rest;

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
    public function __construct(Store $store)
    {
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
         * Check parameter
         */
        $serverParams = $request->getServerParams();

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
                && ('*' != $serverParams[$param] && false == NodeUtils::simpleCheckURI($serverParams[$param]))) {
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
        if (true == NodeUtils::simpleCheckURI($serverParams['o'])
            && false === isset($serverParams['ot'])) {
            return array(
                'message' => 'Bad Request: Parameter o is an URI, so ot must be set.',
                'code' => 400
            );
        }

        // check that ot is either uri or literal
        if (true == NodeUtils::simpleCheckURI($serverParams['o'])
            && false == in_array($serverParams['ot'], array('literal', 'uri'))) {
            return array(
                'message' => 'Bad Request: Parameter ot is neither uri nor literal.',
                'code' => 400
            );
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

            return new Response(' ', 200);

        /*
         * invalid request given.
         */
        } else {
            $response = new Response('php://temp', $result['code']);
            $response->getBody()->write($result['message']);

            return $response;
        }
    }
}
