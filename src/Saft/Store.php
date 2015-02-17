<?php

namespace Saft;

class Store 
{
    /**
     * @var \Saft\Store\Adapter\AbstractAdapter
     */
    protected $adapter;
    
    /**
     * @var array
     */
    protected $availableGraphUris;
    
    /**
     * @var \Saft\Cache
     */
    protected $cache;
    
    /**
     * @var array of \Saft\Store\Graph instances
     */
    protected $graphs;
    
    /**
     * @var \Saft\QueryCache
     */
    protected $queryCache;
    
    /**
     * Add graph.
     * 
     * @param string $graphUri URI of the graph to add.
     * @throw \Exception If given $graphUri parameter is not a valid URI or empty.
     */
    public function addGraph($graphUri)
    {
        if (true === \Saft\Uri::check($graphUri)) {
            
            $this->adapter->addGraph($graphUri);
            
            $this->getAvailableGraphUris();
        } else {
            throw new \Enable\Exception("Given \$graphUri is not a valid URI or empty.");
        }
    }
    
    /**
     * Add multiple triples to a graph.
     * 
     * @param string $graphUri URI of the graph to add triples.
     * @param array $triples Array of triples to add.
     * @return ODBC resource
     * @throw
     */
    public function addMultipleTriples($graphUri, array $triples)
    {
        if (true === $this->isQueryCacheAvailable()) {
            $this->invalidateSubjectResources($graphUri, $triples);
        }
        
        return $this->adapter->addMultipleTriples($graphUri, $triples);
    }
    
    /**
     * Add a triple.
     * 
     * @param string $graphUri URI of the graph to add triple
     * @param string $subject URI of the subject to add
     * @param string $predicate URI of the predicate to add
     * @param array $object Array with data of the object to add
     * @return ODBC resource
     * @throw \Enable\Exception
     */
    public function addTriple($graphUri, $subject, $predicate, array $object)
    {
        return $this->addMultipleTriples(
            $graphUri, array(array($subject, $predicate, $object))
        );
    } 
    
    /**
     * Drops an existing graph.
     * 
     * @param string $graphUri URI of the graph to drop.
     * @throw \Exception If given $graphUri parameter is not a valid URI or empty.
     */
    public function dropGraph($graphUri)
    {
        if (true === \Saft\Uri::check($graphUri)) {
            $this->adapter->dropGraph($graphUri);
            
            /**
             * delete according query cache entries
             */
            if (true === $this->isQueryCacheAvailable()) {
                $this->queryCache->invalidateByGraphUri($graphUri);
            }
            
            // call this to get a fresh list of available graph URIs 
            $this->getAvailableGraphUris();
        } else {
            throw new \Exception("Given \$graphUri is not a valid URI or empty.");
        }
    }
    
    /**
     * Drops multiple triples.
     * 
     * @param string $graphUri URI of the graph to drop triples
     * @param array $triples Array of triples to drop
     * @return ODBC resource Last used ODBC resource
     * @throws \Enable\Exception
     */
    public function dropMultipleTriples($graphUri, array $triples)
    {
        if (true === $this->isQueryCacheAvailable()) {
            $this->invalidateSubjectResources($graphUri, $triples);
        }
        
        return $this->adapter->dropMultipleTriples(
            $graphUri, $triples
        );
    }
    
    /**
     * Drops a triple.
     * 
     * @param string $graphUri URI of the graph to drop the triple
     * @param string $subject URI of the subject to drop
     * @param string $predicate URI of the predicate to drop
     * @param array $object Array with data of the object to drop
     * @return ODBC resource
     * @throw \Enable\Exception
     */
    public function dropTriple($graphUri, $subject, $predicate, array $object)
    {
        return $this->dropMultipleTriples(
            $graphUri, array(array($subject, $predicate, $object))
        );
    }
    
    /**
     * Returns a list of available graph URIs.
     * 
     * @return array List of available graph URIs.
     */
    public function getAvailableGraphUris()
    {
        if (null === $this->availableGraphUris) {
            $this->availableGraphUris = $this->adapter->getAvailableGraphUris();
        }
        return $this->availableGraphUris;
    }
    
    /**
     * Returns active cache instance.
     * 
     * @return \Saft\Cache
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * Returns a graph instance of a graph which belongs to the store.
     *
     * @param string $graphUri URI of the graph to get.
     * @return \Saft\Store\Graph|null
     * @throw \Exception If graph is not available.
     */
    public function getGraph($graphUri)
    {
        if (true === $this->isGraphAvailable($graphUri)) {
            // conserve a once created graph instance and return it, if anybody
            // else want it to later on
            if (false === isset($this->graphs[$graphUri])) {
                $this->graphs[$graphUri] = new \Saft\Store\Graph();
                $this->graphs[$graphUri]->init($this, $graphUri);
            }
            return $this->graphs[$graphUri];
            
        } else {
            throw new \Exception("Graph $graphUri is not available.");
        }
    }
    
    /**
     * 
     * @param string $graphUri
     * @return
     * @throw
     */
    public function getTitleHelper($graphUri)
    {
        if (false === isset($this->_titleHelpers[$graphUri])) {
            // check if graph is available, if so, instantiate it
            if (false === is_null($this->getGraph($graphUri))) {
                $this->_titleHelpers[$graphUri] = new \Enable\Rdf\Property\TitleHelper(
                    $this->getGraph($graphUri), $this->cache
                );
            } else {
                throw new \Enable\Exception(
                    "TitleHelper needs a graph, but no graph with given \$graphUri found."
                );
            }
        }
        
        return $this->_titleHelpers[$graphUri];
    }
    
    /**
     * Either counts triples of a certain graph or all triples of all graphs
     * of the store.
     * 
     * @param string $graphUri
     * @return int number of triples
     * @throw \Enable\Exception
     */
    public function getTripleCount($graphUri = "")
    {
        if (true === \Enable\Utils::isUri($graphUri)) {
            return $this->_adapter->getTripleCount($graphUri);
        } else {
            $graphUris = $this->getAvailableGraphUris();
            $count = 0;
            
            foreach($graphUris as $graphUri) {
                $count += $this->_adapter->getTripleCount($graphUri);
            }
            
            return $count;
        }
    }
    
    /**
     * Imports data into a graph.
     * 
     * @param string $graphUri String with data to import
     * @param string $data String with data to import
     * @param string $format optional RDF-format of the data. Possible are: auto, json, rdfxml, turtle, ntripples, sparql-xml, rdfa
     * @param string $locator optional Set the location of the data. Possible are: datastring, url, file 
     * @throw \Exception If $locator = "file" and file is not readable or does not exists
     *                   If $locator = "datastring" && $format = "auto" and $format is not guessable by $data
     *                   TODO finish
     */
    public function importRdf($graphUri, $data, $format = "auto", $locator = "datastring")
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
                
                $format = \EasyRdf_Format::guessFormat($data);
                
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
                $format = \EasyRdf_Format::getFormat($format);
            // if format is unsupported
            } catch (EasyRdf_Exception $e) {
                throw new \Exception(
                    "Given \$format is not a valid import/export format or empty."
                );
            }
        }
        
        /**
         * at this point we know, what format and/or data we have to handle
         */
        $graph = new \EasyRdf_Graph();
        
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
                    $this->addTriple($graphUri, $subject, $property, $object);
                }
            }
        }
    }
    
    /**
     * 
     */
    public function init(array $config, \Saft\Cache $cache)
    {
        $config = array_merge(array(
            /**
             * Adapters are provided by aditional packages.
             */
            "adapter" => "http",
            
            "dsn" => "",
            
            "username" => "",
            
            "password" => "",
            
        ), $config);
        
        switch ($config["adapter"]) {
            case "http":
                break;
                
            case "virtuoso":
                $this->adapter = new \Saft\Store\Adapter\Virtuoso();
                break;
        }
        
        $this->adapter->init($config);
        $this->cache = $cache;
        
        if (true === $this->isQueryCacheAvailable()) {
            $this->initQueryCache();
        }
    }
    
    /**
     * Initializes QueryCache, if QueryCache is available.
     */
    public function initQueryCache()
    {
        if (true === $this->isQueryCacheAvailable() && null === $this->queryCache) {
            $this->queryCache = new \Saft\QueryCache();
            $this->queryCache->init($this->cache);
        } else {
            // TODO find a way to handle the case user wants to init query cache,
            // but it is not available. Throw exception?
        }
    }
    
    /**
     * Checks if a certain graph is available in the store.
     *
     * @param string $graphUri URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     */
    public function isGraphAvailable($graphUri)
    {
        return $this->adapter->isGraphAvailable($graphUri);
    }
        
    /**
     * Checks if query cache is available. If it is not available install Saft/querycache via composer.
     * 
     * @return boolean
     */
    public function isQueryCacheAvailable()
    {
        return true === class_exists("Saft\\QueryCache");
    }
    
    /**
     * Invalidates cached information about a resource.
     * 
     * @param string $resourceUri URI of the resource to invalidate
     * @return bool
     */
    public function invalidateResource($graphUri, $resourceUri)
    {
        if (true === $this->isQueryCacheAvailable()) {
            return $this->queryCache->invalidateByQuery(
                "SELECT ?p ?o FROM <". $graphUri ."> WHERE {<". $resourceUri ."> ?p ?o.}"
            );
        } else {
            // TODO how to handle case when query cache is not available
            //      throw exception? 
        }
    }
    
    /**
     * Invalidate all query cache entries which refering to given resources (subject).
     * 
     * @param string $graphUri URI of the graph which is related to the triples
     * @param array $tripleArray PHP-array which contains the triples to be created. They will be invalidated first.
     * @throw \Exception
     */
    public function invalidateSubjectResources($graphUri, array $tripleArray)
    {
        $subjectUris = array();
        
        // collect all relevant subject URIs
        foreach ($tripleArray as $triple) {
            // check if subject URI was invalidate before, to prevent obsolete work
            if (false === isset($subjectUris[$triple[0]])) {
                // invalidate resource (triple subject)
                $this->invalidateResource($graphUri, $triple[0]);
                
                // remember triple subject
                $subjectUris[$triple[0]] = $triple[0];
            }
        }
        
        // get according query ids 
        $queryIds = $this->cache->get("queryIds-" . $graphUri);
        
        // check if something is there to delete
        if (null !== $queryIds) {
        
            // get content according to the queryId
            foreach ($queryIds as $queryId) {
                
                // get query container
                $queryContainer = $this->cache->get($queryId);
                
                foreach ($queryContainer["triplePattern"][$graphId] as $pattern) {
                    
                    foreach ($subjectUris as $subjectUri) {
                        $subjectUriId = $this->queryCache->generateShortId($subjectUri, false);
                        
                        // look for the hashed subject URI of a joker sign on the
                        // subjects position ...
                        if (false !== strpos($pattern, $graphUri ."_". $subjectUriId)
                            || false !== strpos($pattern, $graphUri ."_*_")) {
                            // ... in case a match takes place, remove everything,
                            // which is related to the according query of the current
                            // pattern
                            $this->queryCache->invalidateByQuery($queryContainer["query"]);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Send SPARQL query to the server.
     * 
     * @param string $query Query to execute
     * @param array $options optional Options to configure the query-execution and the result.
     * @return array
     * @throw \Exception
     */
    public function sparql($query, array $options = array())
    {
        // if requested, bypass QueryCache
        if (true === isset($options["useQueryCache"]) 
            && false === $options["useQueryCache"]) {
            return $this->adapter->sparql($query, $options);
        }
        
        // if query cache package is available
        if (true === $this->isQueryCacheAvailable()) {
            $this->initQueryCache();
            
            $queryId = $this->queryCache->generateShortId($query);
            $queryResult = $this->cache->get($queryId);
                
            if (null === $queryResult) {
                // execute the query in the store, save the result and init cache entry
                $this->queryCache->rememberQueryResult(
                    $query, $this->adapter->sparql($query, $options)
                );
                $queryResult = $this->cache->get($queryId);
            }
            
            $queryResult = $queryResult["result"];
            
        // if query cache package is NOT available
        } else {
            $queryResult = $this->adapter->sparql($query, $options);
        }
        
        return $queryResult;
    }
}
