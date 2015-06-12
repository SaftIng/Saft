<?php

namespace Saft\Rest;

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
     * Validate given request.
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

        // subject must be * or an URI
        if (false == isset($serverParams['s'])) {
            return array(
                'message' => 'Bad Request: Parameter s not set.',
                'code' => 400
            );
        }

        if (isset($serverParams['s'])
            && ('*' != $serverParams['s'] && false == NodeUtils::simpleCheckURI($serverParams['s']))) {
            return array(
                'message' => 'Bad Request: Parameter s is invalid. Must be * or an URI.',
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
