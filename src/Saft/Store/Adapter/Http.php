<?php

namespace Saft\Store\Adapter;

class Http extends AbstractAdapter
{
    /**
     * Adapter option array
     * 
     * @var array
     */
    protected $_adapterOptions = null;

    /**
     * @var \Saft\Sparql\Client
     */
    protected $client = null;

    /**
     * Constructor.
     * 
     * @param array $adapterOptions Array containing database credentials
     */
    public function __construct(array $adapterOptions)
    {        
        $this->_adapterOptions = $adapterOptions;
        
        // TODO move that config to init function
        $this->client = new \Saft\Sparql\Client();
        
        // Open connection
        $this->connect();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->disconnect();
    }
    
    /**
     * Add a new empty and named graph.
     *
     * @param string $graphUri URI of the graph to create
     * @throws \Exception
     */
    public function addGraph($graphUri)
    {
        $this->client->sendSparqlUpdateQuery("CREATE SILENT GRAPH <". $graphUri .">");
    }
    
    /**
     * Adds multiple triples to the graph.
     * 
     * @param string $graphUri URI of the graph to add the triples
     * @param array $triples Array of triples to add.
     * @return \PDOStatement
     * @throw \Exception
     */
    public function addMultipleTriples($graphUri, array $triples)
    {
        // TODO simplify that mess!
        
        $client = new \Saft\Sparql\Client();
        $client->setUrl($this->_adapterOptions["authUrl"]);
        $response = $client->sendDigestAuthentication(
            $this->_adapterOptions["username"], $this->_adapterOptions["password"]
        );
        
        // validate CURL status
        if(curl_errno($client->getClient()->curl))
            throw new \Exception(curl_error($client->getClient()->error), 500);

        // validate HTTP status code (user/password credential issues)
        $status_code = curl_getinfo($client->getClient()->curl, CURLINFO_HTTP_CODE);
        if ($status_code != 200)
            throw new \Exception("Response with Status Code [" . $status_code . "].", 500);
        
        $client->setUrl($this->_adapterOptions["queryUrl"]);
        
        
        
        
        $tripleNumber = count($triples);
        
        /**
         * create batches
         */ 
        $batch = array();
        $batchSize = 50;

        for($i = 0; $i < $tripleNumber; ++$i) {
            if (0 == $i % $batchSize && 0 < count($batch)) {
                
                $result = $client->sendSparqlUpdateQuery(
                    "INSERT INTO GRAPH <". $graphUri ."> {" .
                        \Saft\Rdf\Triple::buildTripleString($batch) .
                    "}"
                );
                
                $batch = array();
            }
            $batch[] = $triples[$i];
        }
        
        $result = $client->sendSparqlUpdateQuery(
            "INSERT INTO GRAPH <". $graphUri ."> {" .
                \Saft\Rdf\Triple::buildTripleString($batch) .
            "}"
        );

        return $result;
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
    public function addTriple($graphUri, $subject, $predicate, array $object)
    {
        return $this->addMultipleTriples(
            $graphUri, array(array($subject, $predicate, $object))
        );
    }
    
    /**
     * Checks that all requirements for Virtuoso via PDO-ODBC are fullfilled.
     */
    public function checkRequirements()
    {
        // TODO 
        
        return true;
    }  
    
    /**
     * Closes a current connection.
     */
    public function disconnect()
    {
        $this->client->getClient()->close();
    }
    
    /**
     * Drops a graph.
     * 
     * @param string $graphUri URI of the graph to remove
     * @throws \Exception
     */
    public function dropGraph($graphUri)
    {
        $this->client->sendSparqlUpdateQuery("DROP SILENT GRAPH <". $graphUri .">");
    }
    
    /**
     * Drops multiple triples.
     * 
     * @param string $graphUri URI of the graph to drop triples
     * @param array $triples Array of triples to drop
     * @return ODBC resource Last used ODBC resource
     * @throws \Exception
     */
    public function dropMultipleTriples($graphUri, array $triples)
    {      
        // TODO simplify that mess!
        
        $client = new \Saft\Sparql\Client();
        $client->setUrl($this->_adapterOptions["authUrl"]);
        $response = $client->sendDigestAuthentication(
            $this->_adapterOptions["username"], $this->_adapterOptions["password"]
        );
        
        // validate CURL status
        if(curl_errno($client->getClient()->curl))
            throw new \Exception(curl_error($client->getClient()->error), 500);

        // validate HTTP status code (user/password credential issues)
        $status_code = curl_getinfo($client->getClient()->curl, CURLINFO_HTTP_CODE);
        if ($status_code != 200)
            throw new \Exception("Response with Status Code [" . $status_code . "].", 500);
        
        $client->setUrl($this->_adapterOptions["queryUrl"]);
        
        
          
        $tripleNumber = count($triples);
        
        /**
         * create batches
         */ 
        $batch = array();
        $batchSize = 250;

        for($i = 0; $i < $tripleNumber; ++$i) {
            if (0 == $i % $batchSize) {

                $client->sendSparqlUpdateQuery(
                    "DELETE FROM GRAPH <". $graphUri ."> {".
                        \Saft\Rdf\Triple::buildTripleString($batch) .
                    "}"
                );
                 
                $batch = array();
            }
            $batch[] = $triples[$i];
        }
        
        $odbcRes = $client->sendSparqlUpdateQuery(
            "DELETE FROM GRAPH <". $graphUri ."> {".
                \Saft\Rdf\Triple::buildTripleString($batch) .
            "}"
        );

        return $odbcRes;
    }
    
    /**
     * Drops a triple.
     * 
     * @param string $graphUri URI of the graph to drop triple
     * @param string $subject URI of the subject to drop
     * @param string $predicate URI of the predicate to drop
     * @param array $object Array with data of the object to drop
     * @return ODBC resource
     * @throw \Exception
     */
    public function dropTriple($graphUri, $subject, $predicate, array $object)
    {
        return $this->dropMultipleTriples(
            $graphUri, array(array($subject, $predicate, $object))
        );
    }
    
    /**
     * Executes an SPARQL SELECT or SPARQL UPDATE query over HTTP.
     * 
     * @param string $query SPARQL query to execute
     * @param string $type optional Set type of statement: sparql (standard) or sparqlUpdate
     * @return array
     * @throw \Exception If $query is invalid
     *                   If $type = "sql", because it is not supported by SPARQL endpoints
     * @todo merge \PDOStatement and EasyRdf return approachs
     * @todo replace $type = "sparql" with "sparqlSelect"
     */
    public function executeQuery($query, $type = "sparql")
    {
        // SPARQL SELECT query 
        // TODO change $type == "sparql" to "sparqlSelect" (in virtuoso adapter too!)
        if ("sparql" == $type) {
            $responseString = $this->client->sendSparqlSelectQuery($query);
            
            $return = array();
            
            // TODO check result type automatically
            $responseArray = json_decode($responseString, true);
            
            // in case $responseArray is null, something went wrong.
            if (null == $responseArray) {
                throw new \Exception('SPARQL error: '. $responseString);
            } else {

                foreach($responseArray["results"]["bindings"] as $entry) {
                    $returnEntry = array();

                    foreach($responseArray["head"]["vars"] as $var) {
                        $returnEntry[$var] = $entry[$var]["value"];
                    }

                    $return[] = $returnEntry;
                }
            }
        
        // SPARQL UPDATE query 
        } elseif ("sparqlUpdate" == $type) {
            $sparqlResult = $this->client->sendSparqlUpdateQuery($query);
            
            // TODO check HTTP status code
            
            
            // validate CURL status
            //if(curl_errno($curl))
            //    throw new Exception(curl_error($curl), 500);

            // validate HTTP status code (user/password credential issues)
            //$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            //if ($status_code != 200)
            //    throw new Exception("Response with Status Code [" . $status_code . "].", 500);
            
            $return = array();
            
        // SQL queries are not supported by SPARQL endpoints, mostly.
        } elseif ("sql" == $type) {
            throw new \Exception("SQL is not supported.");
        
        // Unknown type
        } else {
            throw new \Exception("Given \$type $type is not supported.");
        }
        
        return $return;
    }
    
    /**
     * 
     */
    public function getAvailableGraphUris()
    {
        $result = $this->executeQuery("SELECT DISTINCT ?graph {GRAPH ?graph { ?s ?p ?o }}");
        
        $graphUris = array();
        foreach ($result as $entry) {
            $graphUris[$entry["graph"]] = $entry["graph"];
        }
        return $graphUris;
    }
    
    /**
     * Returns the URI of which all the queries where send to.
     * 
     * @return string URI on which all queries where send to.
     */
    public function getDsn()
    {
        return $this->client->getUri();
    }
    
    /**
     * Counts the number of triples in a graph.
     *  
     * @param string $graphUri URI of the graph you wanna count triples
     * @return integer Number of found triples
     * @throw \Exception If parameter $graphUri is not valid or empty.
     */
    public function getTripleCount($graphUri)
    {
        if (true === \Saft\Rdf\NamedNode::check($graphUri)) {
            // TODO simplify that mess!
        
            $client = new \Saft\Sparql\Client();
            $client->setUrl($this->_adapterOptions["authUrl"]);
            $response = $client->sendDigestAuthentication(
                $this->_adapterOptions["username"], $this->_adapterOptions["password"]
            );
            
            // validate CURL status
            if(curl_errno($client->getClient()->curl))
                throw new \Exception(curl_error($client->getClient()->error), 500);

            // validate HTTP status code (user/password credential issues)
            $status_code = curl_getinfo($client->getClient()->curl, CURLINFO_HTTP_CODE);
            if ($status_code != 200)
                throw new \Exception("Response with Status Code [" . $status_code . "].", 500);
            
            $client->setUrl($this->_adapterOptions["queryUrl"]);
            
            
            $result = $client->sendSparqlSelectQuery(
                "SELECT (COUNT(*) AS ?count) 
                   FROM <" . $graphUri . "> 
                  WHERE {?s ?p ?o}"
            );
            
            $result = json_decode($result, true);
            return $result["results"]["bindings"][0]["count"]["value"];
        
        } else {
            throw new \Exception("Parameter \$graphUri is not valid or empty.");
        }
    }
    
    /**
     * Init adapter.
     * 
     * @param array $config Array containing database credentials
     * @return
     * @throw \Exception If requirements are not fullfilled. 
     */
    public function init(array $config)
    {
        $this->checkRequirements();
        
        $this->config = $config;
        
        // Open connection
        $this->connect();
    }
    
    /**
     * Checks if a certain graph is available in the store.
     *
     * @param string $graphUri URI of the graph to check if it is available.
     * @return boolean True if graph is available, false otherwise.
     * @todo find a more precise way to check if a graph is available.
     */
    public function isGraphAvailable($graphUri)
    {
        return true === in_array($graphUri, $this->getAvailableGraphUris());
    }
    
    /**
     * Deletes all triples of a graph.
     * 
     * @param string $graphUri URI of the graph to clear.
     * @throw TODO Exceptions 
     */
    public function clearGraph($graphUri)
    {
        $this->executeQuery("CLEAR GRAPH <" . $graphUri . ">", "sparqlUpdate");
    }
    
    /**
     * Returns the current connection resource.
     * @return null
     */
    public function connect()
    {
        $this->client->setUrl($this->_adapterOptions["authUrl"]);
        $this->client->sendDigestAuthentication(
            $this->_adapterOptions["username"], $this->_adapterOptions["password"]
        );
        
        // TODO check if auth was successfully
        
        $this->client->setUrl($this->_adapterOptions["queryUrl"]);
        
        return null;
    }
    
    /**
     * Shortcut for sparqlSelect as long as function was not renamed.
     * 
     * @todo change all sparql-calls to sparqlSelect or sparqlUpdate!
     */
    public function sparql($query, array $options = array())
    {
        return $this->sparqlSelect($query, $options);
    }
        
    /**
     * Send SPARQL query to the server.
     * 
     * @param string $query Query to execute
     * @param array $variables optional Key-value-pairs to create prepared statements
     * @param array $options optional Options to configure the query-execution and the result.
     * @return array
     * @throw \Exception If $options["resultType"] is invalid
     */
    public function sparqlSelect($query, array $options = array())
    {
        /**
         * result type not set, use array instead
         */
        if (false === isset($options["resultType"])) {
            // if nothing was set, array is default result type
            // possible are: array, extended
            $options["resultType"] = "array";
        
        // invalid resultType given
        } elseif ("extended" != $options["resultType"] && "array" != $options["resultType"]){
            throw new \Exception(
                "Given resultType is invalid, allowed are array and extended."
            );
        }
        
        // if result type is set tot extended, use SPARQL client directly because
        // server returns extended result set
        if ("extended" == $options["resultType"]) {
            $result = $this->client->sendSparqlSelectQuery($query);
            // TODO check result type automatically
            $result = json_decode($result, true);
            
        // array == $option["resultType"]
        } else {
            $result = $this->executeQuery($query);
        }
        
        /* TODO simplify and move the following part of enrich result with additional
        //      information
        if ("extended" == $options["resultType"]) {
            $queryInstance = new \Saft\Sparql\Query($query);
            $variables = $queryInstance->getVariables();
            
            // build extended result
            $extendedResult = array(
                "head" => array(
                    "link" => array(), // TODO what is head->link?
                    "vars" => $variables
                ),
                "results" => array(
                    "distinct"  => false, // TODO get that from the query
                    "ordered"   => "" !== $queryInstance->getOrderClause(),
                    "bindings"  => array()
                )
            );
            
            // go through result entries
            foreach($result["results"]["bindings"] as $entry) {
                $binding = array();
                
                // go through all variables from the SELECT clause like ?s
                foreach($variables as $var) {
                    
                    // if result entry hasnt one of the variables set
                    // this seems to happen for SPARQL queries which emulate 
                    // language gathering for objects (see Graph->getResourceInformation(...))
                    if (false === isset($entry[$var])) {
                        $binding[$var] = array(
                            "type" => "literal",
                            "value" => null // @TODO use "" instead of null?
                        );
                    
                    // result entry's $var field is set ...
                    } else {
                    
                        $binding[$var] = array(
                            "type" => null,
                            "value" => $entry[$var]["value"]
                        );
                        
                        if (true === \Saft\Uri::check($entry[$var]["value"])) {
                            $binding[$var]["type"] = "uri";
                    
                        // emulate a function to get language information to objects
                        // in a sparql result.
                        } elseif ("saftLang" == $var
                                  && false === empty($entry["saftLang"]["value"])) {
                            $binding["o"]["lang"] = $entry["saftLang"]["value"];
                            
                            $binding["o"]["type"] = "literal";
                            unset($binding["o"]["datatype"]);
                        
                        // $result = array(
                        //      0 => array(
                        //          "s" => array(..)               <==
                        //          "p" => ..
                        //      )
                        // )
                        } else {
                            $binding[$var]["datatype"] = \EasyRdf\Literal::getDatatypeForValue(
                                $entry[$var]["value"]
                            );
                            
                            // check for string which only contains numbers. in that
                            // case set xsd:integer instead of xsd:string
                            if (true === is_string($entry[$var]["value"]) 
                                && true === ctype_digit($entry[$var]["value"])) {
                              $binding[$var]["datatype"] = "http://www.w3.org/2001/XMLSchema#integer";
                              
                            // additional check for strings, because EasyRdf does not
                            // care for strings and returns null.
                            // Pending pull request for EasyRdf:
                            // https://github.com/njh/easyrdf/pull/236
                            } elseif (true === is_string($entry[$var]["value"])) {
                                $binding[$var]["datatype"] = "http://www.w3.org/2001/XMLSchema#string";
                            }
                            
                            $binding[$var]["type"] = "typed-literal";
                        }
                    }
                }
                
                $extendedResult["results"]["bindings"][] = $binding;
            }
            
            $result = $extendedResult;
        }*/
        
        return $result;
    }
}
