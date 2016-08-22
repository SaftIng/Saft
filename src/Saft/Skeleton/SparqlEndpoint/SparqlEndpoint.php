<?php

namespace Saft\Skeleton\SparqlEndpoint;

use Negotiation\Negotiator;
use Saft\Skeleton\Data\SerializerFactory;
use Saft\Store\Store;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SparqlEndpoint
{
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
    public function __construct(Store $store, SerializerFactory $serializerFactory)
    {
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
     * @param StoRequestre $request
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
}
