<?php

namespace Saft\Skeleton\Rest;

use Saft\Data\ParserFactory;
use Saft\Data\SerializerFactory;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\StatementFactory;
use Saft\Store\Store;
use Saft\Sparql\Query\QueryFactory;

use Slim\Slim;

/**
 * This class provides a REST interface for the Saft.store
 *
 * The interface is inspered by the SPARQL HTTP interface and should be backward compatible
 * {@url http://www.w3.org/TR/2013/REC-sparql11-http-rdf-update-20130321/}
 *
 * It is documented at {@url http://safting.github.io/doc/restinterface/triplestore/}
 * ?query=:query&... : Store->query($query, $options[...]);
 * ?action=:action
 * ?s=:s&p=:p&o=:o&graph=:g : Store->getMatchingStatements($s, $p, $o, $g, $options[…]);
 */
class Application
{
    private $store;
    private $nf;
    private $sf;
    private $parf;
    private $serf;

    public function __construct(Store $store, NodeFactory $nf, StatementFactory $sf, ParserFactory $parf, SerializerFactory $serf)
    {
        $this->store = $store;
        $this->nf = $nf;
        $this->sf = $sf;
        $this->parf = $parf;
        $this->serf = $serf;
    }

    public function run()
    {
        $app = new Slim();
        $app->map('/', function () use ($app) {
            $arguments = [
                'graph' => $app->request()->params('graph'),
                'query' => $app->request()->params('query'),
                'action' => $app->request()->params('action'),
                'subject' => $app->request()->params('s'),
                'predicate' => $app->request()->params('p'),
                'object' => $app->request()->params('o'),
                'bodyType' =>  $app->request->headers->get('Content-Type'),
                'body' => $app->request()->getBody(),
            ];

            // set default action
            if ($arguments['action'] == null) {
                $arguments['action'] = 'get';
                if ($arguments['query'] != null) {
                    $arguments['action'] = 'query';
                }
            }

            $callAction = strtolower($arguments['action']). 'Action';
            $action = new \ReflectionMethod($this, $callAction);
            $methodParameters = $action->getParameters();
            $callParameters = array();
            foreach ($methodParameters as $parameter) {
                $name = $parameter->getName();
                if ($arguments[$name] === null && $parameter->isDefaultValueAvailable()) {
                    $callParameters[] = $parameter->getDefaultValue();
                } else {
                    $callParameters[] = $arguments[$name];
                }
            }

            $return = call_user_func_array([$this, $callAction], $callParameters);

            echo $this->serializeResult($return, $app);
        })->via('GET', 'POST', 'PUT');
        $app->run();
    }

    protected function serializeResult($result, $app)
    {
        $acceptHeader = $app->request->headers->get('Accept');
        if ($result instanceof \Saft\Rdf\StatementIterator) {
            // TODO set correct mime type
            $serializer = $this->serf->createSerializerFor('ntriples');
            $app->response->headers->set('Content-Type', 'application/n-triple');
            $serializer->serializeIteratorToStream($result, 'php://output', 'ntriples');
            return;
        } elseif ($result instanceof \Saft\Sparql\Result) {
            // TODO transform result to correct JSON result or whatever has to be returned
            return var_export($result, true);
        }
        return json_encode($result);

        // maybe we should return a general success message if no Exception was thrown
        // How to handle Exceptions?
    }

    public function getAction($subject, $predicate, $object, $graph = null)
    {
        // TODO evaluate accept header to decide which serialization to use
        $graphNode = null;
        if ($graph != null) {
            $graphNode = $this->nf->createNamedNode($graph);
        }

        $pattern = $this->createStatement($subject, $predicate, $object);

        return $this->store->getMatchingStatements($pattern, $graphNode);
    }

    public function hasAction($subject, $predicate, $object, $graph = null)
    {
        $graphNode = null;
        if ($graph != null) {
            $graphNode = $this->nf->createNamedNode($graph);
        }
        $pattern = $this->createStatement($subject, $predicate, $object);
        return $this->store->hasMatchingStatements($pattern, $graphNode);
    }

    public function addAction($body, $bodyType = 'text/turtle', $graph = null)
    {
        $graphNode = null;
        if ($graph != null) {
            $graphNode = $this->nf->createNamedNode($graph);
        }
        $mimeToSerialization = [
            'text/turtle' => 'turtle',
            'application/rdf+xml' => 'rdfxml',
        ];
        $serialization = 'turtle';
        if (isset($mimeToSerialization[$bodyType])) {
            $serialization = $mimeToSerialization[$bodyType];
        }
        $parser = $this->parf->createParserFor($serialization);
        $statements = $parser->parseStringToIterator($body, null, $serialization);
        $this->store->addStatements($statements, $graphNode);
    }

    public function deleteAction($subject, $predicate, $object, $graph = null)
    {
        $graphNode = null;
        if ($graph != null) {
            $graphNode = $this->nf->createNamedNode($graph);
        }
        $pattern = $this->createStatement($subject, $predicate, $object);
        $this->store->deleteMatchingStatements($pattern, $graphNode);
    }

    public function queryAction($query, $graph = null)
    {
        $graphNode = null;
        if ($graph != null) {
            $graphNode = $this->nf->createNamedNode($graph);
        }
        return $this->store->query($query, $graphNode);
    }

    public function getGraphsAction()
    {
        $graphs = $this->store->getGraphs();
        $list = array();
        foreach ($graphs as $graph) {
            $list[] = $graph->getUri();
        }
        return $list;
    }

    public function createGraphAction($graph)
    {
        $graphNode = $this->nf->createNamedNode($graph);
        $this->store->createGraph($graphNode);
    }

    public function dropGraphAction($graph)
    {
        $graphNode = $this->nf->createNamedNode($graph);
        $this->store->dropGraph($graphNode);
    }

    protected function createStatement($subject, $predicate, $object)
    {
        $s = $this->nf->createAnyPattern();
        $p = $this->nf->createAnyPattern();
        $o = $this->nf->createAnyPattern();

        if ($subject != null) {
            $s = $this->nf->createNodeFromNQuads($subject);
        }
        if ($predicate != null) {
            $p = $this->nf->createNodeFromNQuads($predicate);
        }
        if ($object != null) {
            $o = $this->nf->createNodeFromNQuads($object);
        }

        return $this->sf->createStatement($s, $p, $o);
    }
}
