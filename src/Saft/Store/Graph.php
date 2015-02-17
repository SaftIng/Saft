<?php

namespace Saft\Store;

class Graph
{   
    /**
     * @var string
     */
    protected $graphUri = "";
    
    /**
     * @var \Saft\Store
     */
    protected $store = null;
    
    /**
     * Adds multiple triples to this graph.
     * 
     * @param array $triples Array of triples
     * @return ODBC-Resource
     * @throw \Exception
     */
    public function addMultipleTriples(array $triples)
    {
        return $this->store->addMultipleTriples($this->graphUri, $triples);
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
        return $this->store->addMultipleTriples(
            $this->graphUri, array(array($subject, $predicate, $object))
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
        return $this->store->dropMultipleTriples($this->graphUri, $triples);
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
        return $this->store->dropTriple(
            $this->graphUri, $subject, $predicate, $object
        );
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
            $query = "SELECT ?p ?o FROM <". $this->graphUri ."> WHERE {<". $resourceUri ."> ?p ?o.}";
            $queryId = $this->store->getQueryCache()->generateShortId($query);
            $queryResult = $store->getCache()->get($queryId);
            
            // check if resource uri was saved before, only save it once
            if (false === $queryResult) {
                
                $queryResult = null;
                
                $result = $this->sparql(
                    $query, 
                    array("resultType" => "extended")
                );
                $result = $result["results"]["bindings"];
                
                // if result contains information, compute them
                if (0 < count($result)) {
                    
                    $queryResult = array();
                    
                    foreach ($result as $triple) {
                        // add triple
                        $queryResult[] = array(
                            $resourceUri,
                            $triple["p"]["value"],
                            array(
                                "lang"      => true === isset($triple["o"]["xml:lang"]) 
                                               ? $triple["o"]["xml:lang"] : null,
                                "datatype"  => true === isset($triple["o"]["datatype"]) 
                                               ? $triple["o"]["datatype"] : null,
                                "type"      => $triple["o"]["type"],
                                "value"     => $triple["o"]["value"]
                            )
                        );
                    }
                }
                
                // init cache entry
                $this->store->getQueryCache()->rememberQueryResult(
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
        return $this->store->getTripleCount($this->graphUri);
    }
    
    /**
     * @return \Enable\Store
     */
    public function getStore()
    {
        return $this->store;
    }
    
    /**
     * Return URI of the graph.
     * @return
     */
    public function getUri()
    {
        return $this->graphUri;
    }
    
    /**
     * Imports data into a graph.
     * 
     * @see \Saft\Store->importRdf for more information
     */
    public function importRdf($data, $format = "auto", $locator = "datastring")
    {
        $this->store->importRdf($this->graphUri, $data, $format, $locator);
    }
        
    /**
     * Initialize graph instance.
     * 
     * @param \Saft\Store $store According store instance.
     * @param string $graphUri URI of the graph
     * @throw \Exception
     */
    public function init(\Saft\Store $store, $graphUri)
    {
        $this->setGraphUri($graphUri);
        
        $this->setStore($store);
    }
    
    /**
     * Set graph URI.
     * 
     * @param string $graphUri URI to set.
     * @throw \Exception If given $graphUri parameter is not a valid URI or empty.
     */
    protected function setGraphUri($graphUri)
    {
        if (true === \Saft\Uri::check($graphUri)) {
            $this->graphUri = $graphUri;
        } else {
            throw new \Exception(
                "Given \$graphUri is not a valid URI or empty."
            );
        }
    }
    
    /**
     * Set new store instance.
     * 
     * @param \Saft\Store $store Store instance which this graph instance has to use
     */
    protected function setStore(\Saft\Store $store)
    {
        $this->store = $store;
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
        // force FROM clause of the SPARQL to be set to the value of $this->graphUri
        $queryObj = new \Saft\Sparql\Query();
        $queryObj->init($query);
        $queryObj->setFrom(array($this->graphUri));
        
        return $this->store->sparql((string) $queryObj, $options);
    }
    
}
