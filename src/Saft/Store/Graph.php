<?php

namespace Saft\Store;

class Graph
{
    /**
     * @var \Enable\Cache
     */
    protected $_cache;
    
    /**
     * @var string
     */
    protected $_graphUri = "";
    
    /**
     * @var string
     */
    protected $_graphUriHash = "";
    
    /**
     * @var \Enable\Store
     */
    protected $_store = null;
    
    /**
     * 
     * @param
     * @return
     * @throw
     */
    public function __construct(\Saft\Store $store, $graphUri, \Saft\Cache $cache)
    {
        $this->init($store, $graphUri, $cache);
    }
    
    /**
     * Adds multiple triples to this graph.
     * 
     * @param array $triples Array of triples
     * @return ODBC-Resource
     * @throw \Exception
     */
    public function addMultipleTriples(array $triples)
    {
        return $this->_store->addMultipleTriples($this->_graphUri, $triples);
    }
    
    /**
     * Add a triple.
     * 
     * @param string $graphUri URI of the graph to add triple
     * @param string $subject URI of the subject to add
     * @param string $predicate URI of the predicate to add
     * @param array $object Array with data of the object to add
     * @return ODBC resource
     * @throw \Exception
     */
    public function addTriple($subject, $predicate, array $object)
    {
        return $this->_store->addMultipleTriples(
            $this->_graphUri, array(array($subject, $predicate, $object))
        );
    } 
    
    /**
     * Drops multiple triples.
     * 
     * @param array $triples Array of triples to drop
     * @return ODBC resource Last used ODBC resource
     * @throws \Exception
     */
    public function dropMultipleTriples(array $triples)
    {
        return $this->_store->dropMultipleTriples($this->_graphUri, $triples);
    }    
    
    /**
     * Drops a triple.
     * 
     * @param string $subject URI of the subject to drop
     * @param string $predicate URI of the predicate to drop
     * @param array $object Array with data of the object to drop
     * @return ODBC resource
     * @throw \Exception
     */
    public function dropTriple($subject, $predicate, $object)
    {
        return $this->_store->dropTriple(
            $this->_graphUri, $subject, $predicate, $object
        );
    }    
    
    /**
     * Generates unique resource id.
     * @param string $resourceUri
     * @return string Unique resource id + cache prefix
     */
    public function generateResourceId($resourceUri)
    {
        return "sto". $this->_store->getId() . "--" . 
               "gph". $this->_graphUriHash   . "_" . 
               md5($resourceUri);
    } 
    
    /**
     * Based on the given URI this function load information (?p, ?o) about a certain
     * resource.
     * 
     * @param string $resourceUri URI of the resource to gather information.
     * @param string $lang optional Language tag
     * @return array
     * @throw \Exception if resource URI is not valid or empty.
     */
    public function getResourceInformation($resourceUri, $lang = "")
    {
        if (true === \Saft\Uri::check($resourceUri)) {
        
            // generate unique ID for the resource uri
            $query = "SELECT ?p ?o ".
                       "FROM <". $this->_graphUri ."> ".
                      "WHERE {<". $resourceUri ."> ?p ?o.} ".
                      "ORDER BY ?p";
                      
            $queryId = $this->_store->getQueryCache()->generateShortId($query);
            
            $queryResult = $this->_cache->get($queryId);
            
            // check if resource uri was saved before, only save it once
            if (false === $queryResult) {
                
                $queryResult = null;
                
                $result = $this->sparql($query, array("resultType" => "extended"));
                $result = $result["results"]["bindings"];
                
                // if result contains information, compute them
                if (0 < count($result)) {
                    
                    $queryResult = array();
                    
                    foreach ($result as $triple) {
                        
                        // TODO add switch for xml:lang (virtuoso) and lang (http)
                        
                        // add triple
                        $queryResult[] = array(
                            $resourceUri,
                            $triple["p"]["value"],
                            array(
                                "lang"      => true === isset($triple["o"]["lang"]) 
                                               ? $triple["o"]["lang"] : null,
                                "datatype"  => true === isset($triple["o"]["datatype"]) 
                                               ? $triple["o"]["datatype"] : null,
                                "type"      => $triple["o"]["type"],
                                "value"     => $triple["o"]["value"]
                            )
                        );
                    }
                }
                
                // init cache entry
                $this->_store->getQueryCache()->rememberQueryResult(
                    $query, $queryResult
                );
                
            } else {
                $queryResult = $queryResult["result"];
            }
            
            return $queryResult;
            
        } else {
            throw new \Exception("Given \$resourceUri is not an URI or empty.");
        }
    }
    
    /**
     * Counts the number of triples in this graph.
     * 
     * @return int 
     * @throw \Exception
     */
    public function getTripleCount()
    {
        return $this->_store->getTripleCount($this->_graphUri);
    }
    
    /**
     * @return \Enable\Store
     */
    public function getStore()
    {
        return $this->_store;
    }
    
    /**
     * Return URI of the graph.
     * @return
     */
    public function getUri()
    {
        return $this->_graphUri;
    }
    
    /**
     * Imports data into a graph
     * @param string $rdf              String with data to import
     * @param string $format  optional RDF-format of the data. Possible are: 
     *                                 auto, json, rdfxml, turtle, ntripples, 
     *                                 sparql-xml, rdfa
     * @param string $locator optional Set the location of the data. Possible are:
     *                                 datastring, url, file 
     * @return
     * @throw
     */
    public function importRdf($data, $format = "auto", $locator = "datastring")
    {
        if ("auto" === $format) {
                
            // url
            if ($locator === "url") {
                
                // no format guessing neccessary, because EasyRdf will do it
                // for us
                
            // file
            } elseif ($locator === "file") {
                
                // check if file is readable and exists
                if (false === is_readable($data) || false === file_exists($data)) {
                    throw new \Exception(
                        "File to import does not exists or not readable."
                    );
                }
                
                // no format guessing neccessary, because EasyRdf will do it
                // for us
            
            // data string
            } elseif ($locator === "datastring") {
                
                $format = \EasyRdf\Format::guessFormat($data);
                
                if (null === $format) {
                    throw new \Exception(
                        "\$format is not guessable from the given \$data."
                    );
                }
                
            // unsupported locator
            } else {
                
                throw new \Exception(
                    "Given \$locator is not a valid locator or empty."
                );
            }
            
        // user defined format
        } else {
            
            // normalize given format; we support synonyms which means, that
            // rdfxml and xml both leads to rdfxml
            try {
                $format = \EasyRdf\Format::getFormat($format);
            // if format is unsupported
            } catch (\EasyRdf\Exception $e) {
                throw new \Exception(
                    "Given \$format is not a valid import/export format or empty."
                );
            }
        }
        
        /**
         * at this point we know, what format and/or data we have to handle
         */
        $graph = new \EasyRdf\Graph();
        
        // file
        if ($locator === "file") {
            if ("auto" === $format) {
                $graph->parseFile($data);
            } else {
                $graph->parseFile($data, $format->getName());
            }
        
        // data string
        } elseif ($locator === "datastring") {
            $graph->parse($data, $format->getName());
        
        // url
        } elseif ($locator === "url") {
            if ("auto" === $format) {
                $graph->load($data);
            } else {
                $graph->load($data, $format->getName());
            }
            
        // unsupported locator
        } else {
            throw new \Exception(
                "Given \$locator is not a valid locator or empty."
            );
        }
        
        // move data from $graph instance to graph        
        $data = $graph->toRdfPhp();
        
        // go through all subjects
        foreach ($data as $subject => $predicates) {
            // predicates associated with the subject
            foreach ($predicates as $property => $objects) {
                // object(s)
                foreach ($objects as $object) {
                    $this->addTriple($subject, $property, $object);
                }
            }
        }
    }
        
    /**
     * Initialize graph instance.
     * 
     * @param \Enable\Store $store
     * @param string $graphUri
     * @param \Enable\Cache $cache
     * @return
     * @throw \Exception
     */
    public function init(\Saft\Store $store, $graphUri, \Saft\Cache $cache)
    {
        $this->setGraphUri($graphUri);
        $this->_graphUriHash = md5($graphUri);
        
        $this->setCache($cache);
        $this->setStore($store);
    }
    
    /**
     * Invalidates cached information about a resource.
     * 
     * @param string $resourceUri URI of the resource to invalidate
     * @return bool
     */
    public function invalidateResource($resourceUri)
    {
        return $this->_store->getQueryCache()->invalidateByQuery(
            "SELECT ?p ?o FROM <". $this->_graphUri ."> WHERE {<". $resourceUri ."> ?p ?o.}"
        );
    }
    
    /**
     * 
     * @param
     * @return
     * @throw
     */
    protected function setCache(\Saft\Cache $cache)
    {
        $this->_cache = $cache;
    }
    
    /**
     * 
     * @param
     * @return
     * @throw
     */
    protected function setGraphUri($graphUri)
    {
        if (true === \Saft\Uri::check($graphUri)) {
            $this->_graphUri = $graphUri;
        } else {
            throw new \Exception(
                "Given \$graphUri is not a valid URI or empty."
            );
        }
    }
    
    /**
     * Set new store instance.
     * 
     * @param \Enable\Store $store Store instance which this graph instance has to use
     * @return void
     */
    protected function setStore(\Saft\Store $store)
    {
        $this->_store = $store;
    }
    
    /**
     * Send SPARQL query to the server.
     * 
     * @param string $query Query to execute
     * @param array $options optional Options to configure the query-execution and the 
     *                                result.
     * @return array
     * @throw \Exception
     */
    public function sparql($query, array $options = array())
    {
        // force FROM clause of the SPARQL to be set to the value of $this->_graphUri
        $queryObj = new \Saft\Sparql\Query($query);
        $queryObj->setFrom(array($this->_graphUri));
        
        return $this->_store->sparql((string) $queryObj, $options);
    }
    
}
