<?php

namespace Saft\Sparql;

use Saft\Rdf\AbstractNamedNode;

/**
 * This file WAS part of the {@link http://erfurt-framework.org Erfurt} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class models a SPARQL query that can be used within an application in order to make
 * it easier e.g. to set different parts of a query independently.
 *
 * @author    Norman Heino <norman.heino@gmail.com>
 * @author    Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Query
{
    /**
     * @var array
     */
    protected $from = array();

    /**
     * @var array
     */
    protected $fromNamed = array();

    /**
     * @var int
     */
    protected $limit = null;
    
    /**
     * @var string
     */
    protected $orderClause = null;
    
    /**
     * List of widely used namespaces.
     *
     * @var
     */
    protected $namespaces = array(
        'bibo'    => 'http://purl.org/ontology/bibo/',
        'cc'      => 'http://creativecommons.org/ns#',
        'cert'    => 'http://www.w3.org/ns/auth/cert#',
        'ctag'    => 'http://commontag.org/ns#',
        'dc'      => 'http://purl.org/dc/terms/',
        'dc11'    => 'http://purl.org/dc/elements/1.1/',
        'dcat'    => 'http://www.w3.org/ns/dcat#',
        'dcterms' => 'http://purl.org/dc/terms/',
        'doap'    => 'http://usefulinc.com/ns/doap#',
        'exif'    => 'http://www.w3.org/2003/12/exif/ns#',
        'foaf'    => 'http://xmlns.com/foaf/0.1/',
        'geo'     => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
        'gr'      => 'http://purl.org/goodrelations/v1#',
        'grddl'   => 'http://www.w3.org/2003/g/data-view#',
        'ical'    => 'http://www.w3.org/2002/12/cal/icaltzd#',
        'ma'      => 'http://www.w3.org/ns/ma-ont#',
        'og'      => 'http://ogp.me/ns#',
        'org'     => 'http://www.w3.org/ns/org#',
        'owl'     => 'http://www.w3.org/2002/07/owl#',
        'prov'    => 'http://www.w3.org/ns/prov#',
        'qb'      => 'http://purl.org/linked-data/cube#',
        'rdf'     => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'rdfa'    => 'http://www.w3.org/ns/rdfa#',
        'rdfs'    => 'http://www.w3.org/2000/01/rdf-schema#',
        'rev'     => 'http://purl.org/stuff/rev#',
        'rif'     => 'http://www.w3.org/2007/rif#',
        'rr'      => 'http://www.w3.org/ns/r2rml#',
        'rss'     => 'http://purl.org/rss/1.0/',
        'schema'  => 'http://schema.org/',
        'sd'      => 'http://www.w3.org/ns/sparql-service-description#',
        'sioc'    => 'http://rdfs.org/sioc/ns#',
        'skos'    => 'http://www.w3.org/2004/02/skos/core#',
        'skosxl'  => 'http://www.w3.org/2008/05/skos-xl#',
        'synd'    => 'http://purl.org/rss/1.0/modules/syndication/',
        'v'       => 'http://rdf.data-vocabulary.org/#',
        'vcard'   => 'http://www.w3.org/2006/vcard/ns#',
        'void'    => 'http://rdfs.org/ns/void#',
        'wdr'     => 'http://www.w3.org/2007/05/powder#',
        'wdrs'    => 'http://www.w3.org/2007/05/powder-s#',
        'wot'     => 'http://xmlns.com/wot/0.1/',
        'xhv'     => 'http://www.w3.org/1999/xhtml/vocab#',
        'xml'     => 'http://www.w3.org/XML/1998/namespace',
        'xsd'     => 'http://www.w3.org/2001/XMLSchema#',
    );

    /**
     * @var int
     */
    protected $offset = null;
    
    /**
     * @var string
     */
    protected $prologuePart = null;

    /**
     * @var string
     */
    protected $query = '';

    /**
     * @var array
     */
    protected $queryParts;
    
    /**
     * Type of the query. Can be either ask, select, describe, insertInto, insertData, delete, deleteData
     *
     * @var string
     */
    protected $type = null;
    
    /**
     * @var string
     */
    protected $wherePart = null;

    /**
     */
    public function __construct($queryString = '')
    {
        if (false === empty($queryString)) {
            $this->init($queryString);
        }
    }

    /**
     *
     */
    public function __toString()
    {
        $queryString = $this->prologuePart . PHP_EOL;

        foreach (array_unique($this->from) as $from) {
            $queryString .= 'FROM <' . $from . '>' . PHP_EOL;
        }

        foreach (array_unique($this->fromNamed) as $fromNamed) {
            $queryString .= 'FROM NAMED <' . $fromNamed . '>' . PHP_EOL;
        }

        $queryString .= $this->wherePart . ' ';

        if ($this->orderClause !== null) {
            $queryString .= 'ORDER BY ' . $this->orderClause . PHP_EOL;
        }

        if ($this->limit !== null) {
            $queryString .= 'LIMIT ' . $this->limit . PHP_EOL;
        }

        if ($this->offset !== null) {
            $queryString .= 'OFFSET ' . $this->offset . PHP_EOL;
        }

        return $queryString;
    }

    public function addFrom($iri)
    {
        $this->from[] = $iri;

        return $this;
    }

    public function addFromNamed($iri)
    {
        $this->fromNamed[] = $iri;

        return $this;
    }
    
    /**
     * Determines type of a entity.
     *
     * @param string $entity Entity string.
     * @return string|null Returns either literal, typed-literal, uri or var. Returns null if it couldnt be
     *                     determined.
     */
    public function determineEntityType($entity)
    {
        // checks if $entity is an URL
        if (true === AbstractNamedNode::check($entity)) {
            return 'uri';
        
        // checks if ^^< is in $entity OR if $entity is surrounded by quotation marks
        } elseif (false !== strpos($entity, '"^^<')
            || ('"' == substr($entity, 0, 1)
                && '"' == substr($entity, strlen($entity)-1, 1))) {
            return 'typed-literal';
            
        // checks if "@ is in $entity
        } elseif (false !== strpos($entity, '"@')) {
            return 'literal';
            
        // checks if $entity is a string; only strings can be a variable
        } elseif (true === is_string($entity)) {
            return 'var';
            
        // unknown type
        } else {
            return null;
        }
    }
    
    /**
     * Determines SPARQL datatype of a given string, usually of an object value.
     *
     * @param string $objectString Object value, incl. datatype information.
     * @return string|null Returns datatype of object, e.g. http://www.w3.org/2001/XMLSchema#string. Returns
     *                     null if datatype couldnt be determined.
     */
    public function determineObjectDatatype($objectString)
    {
        $datatype = null;
        
        // checks if ^^< is in $objectString
        $arrowPos = strpos($objectString, '"^^<');
        
        // checks if $objectString starts with " and contains "^^<
        if ('"' === substr($objectString, 0, 1) && false !== $arrowPos) {
            // extract datatype URI
            $datatype = substr($objectString, $arrowPos + 4);
            return substr($datatype, 0, strlen($datatype)-1);
            
        // checks for surrounding ", without ^^<
        } elseif ('"' == substr($objectString, 0, 1)
            && '"' == substr($objectString, strlen($objectString)-1, 1)) {
            // if we land here, there are surrounding quotation marks, but no datatype
            return 'http://www.w3.org/2001/XMLSchema#string';
        
        // malformed string, return null as datatype
        } else {
            return null;
        }
    }
    
    /**
     * Determines SPARQL language of a given string, usually of an object value.
     *
     * @param string $objectString Object value, incl. language information.
     * @return string|null Returns language of object, e.g. en. Returns null if language couldnt be determined.
     */
    public function determineObjectLanguage($objectString)
    {
        $atPos = strpos($objectString, '"@');
        
        // check for language @
        if (false !== $atPos) {
            $language = substr($objectString, $atPos + 2);
            return 2 <= strlen($language) ? $language : null;
        }
        
        return null;
    }
    
    /**
     * Determines SPARQL language of a given string, usually of an object value.
     *
     * @param string $objectString Object value, incl. language information.
     * @return string Returns value of object. Returns null, if value couldnt be determined.
     */
    public function determineObjectValue($objectString)
    {
        // checks if ^^< is in $objectString
        $arrowPos = strpos($objectString, '"^^<');
        $atPos = strpos($objectString, '"@');
        
        // checks if $objectString starts with " and contains "^^<
        if ('"' === substr($objectString, 0, 1) && false !== $arrowPos) {
            return substr($objectString, 1, $arrowPos-1);
            
        // checks for surrounding ", without ^^<
        } elseif ('"' == substr($objectString, 0, 1)
            && '"' == substr($objectString, strlen($objectString)-1, 1)) {
            return substr($objectString, 1, strlen($objectString)-2);
            
        // checks for "@
        } elseif (false !== $atPos) {
            return substr($objectString, 1, $atPos - 1);
        
        // checks for ? at the beginning
        } elseif ('?' === substr($objectString, 0, 1)) {
            return substr($objectString, 1);
        
        // malformed string, return null as datatype
        } else {
            return null;
        }
    }

    /**
     * Extracts dataset from query, which are named and unnamed graph URIs.
     *
     * @param  array $fromEntries
     * @param  array $fromNamedEntries
     * @return array Array of array which contains graphUri's and if they are named or not.
     */
    public function extractDatasetsFromQuery(array $fromEntries, array $fromNamedEntries)
    {
        $datasets = array();

        foreach ($fromEntries as $entry) {
            $datasets[] =  array('graph' => $entry, 'named' => 0);
        }

        foreach ($fromNamedEntries as $entry) {
            $datasets[] =  array('graph' => $entry, 'named' => 1);
        }

        return $datasets;
    }

    /**
     * Extracts filter pattern out of a given string.
     *
     * @param  string $wherePart WHERE part of the query.
     * @return array
     * @todo   simplify function
     */
    public function extractFilterPattern($wherePart)
    {
        $pattern = array();

        preg_match_all(
            '/FILTER(\s*|\s*[a-zA-Z0-9]*)\((.*)\)/',
            $wherePart,
            $matches
        );

        foreach ($matches[2] as $match) {
            /**
             * Covers filters such as:
             * - FILTER (?o < 40)
             * - FILTER (?s = "foo")
             */
            preg_match_all(
                '/' .
                '(\?[a-zA-Z0-9\_]+)'.   // e.g. ?s
                '\s*' .                 // space
                '(=|<|>|!=)' .          // operator, e.g. =
                '\s*' .                 // space
                '(".*"|[0-9]*)' .       // constrain, e.g. 40 or "Bar"
                '/',
                $match,
                $parts
            );

            if (true == isset($parts[3][0])) {
                $entry = array('type' => 'filter', 'constraint' => array(
                    'type'      => 'expression',
                    'sub_type'  => 'relational',
                    'patterns'  => array(
                        array(
                            'value'     => substr($parts[1][0], 1), // e.g. ?s
                            'type'      => 'var', // its always a variable
                            'operator'  => ''
                        ),
                        array(
                            'value'     => str_replace('"', '', $parts[3][0]), // e.g. "Bar"
                            'type'      => 'literal',
                            'operator'  => ''
                        )
                    ),
                    'operator'  => $parts[2][0] // operator, e.g. <
                ));

                // set datatype and sub_type
                if (true === ctype_digit($parts[3][0])) {
                    $entry['constraint']['patterns'][1]['datatype'] =
                        'http://www.w3.org/2001/XMLSchema#integer';
                } else {
                    $entry['constraint']['patterns'][1]['sub_type'] = 'literal2';
                }

                $pattern[] = $entry;

                // go to the next match
                continue;
            }
        }

        /**
         * Covers regex filters such as:
         * - FILTER regex(?g, "aar")
         * - FILTER regex(?g, "aar", "i")
         */
        preg_match_all(
            '/regex\s*\((\?[a-zA-Z0-9]*),\s*"([^"]*)"(,\s*"(.*)")*\)/',
            $wherePart,
            $matches
        );

        if (true == isset($matches[1][0])) {
            $entry = array('type' => 'filter', 'constraint' => array(
                'args' => array(
                    array(
                        'value' => substr($matches[1][0], 1),
                        'type' => 'var',
                        'operator' => ''
                    ),
                    array(
                        'value' => $matches[2][0],
                        'type' => 'literal',
                        'sub_type' => 'literal2',
                        'operator' => ''
                    )
                ),
                'type' => 'built_in_call',
                'call' => 'regex',
            ));

            // if optional part is set, which means the regex function gots 3 parameter
            if (true === isset($matches[4][0])) {
                $entry['constraint']['args'][] = array(
                    'value' => $matches[4][0],  // optional part, i
                    'type' => 'literal',
                    'sub_type' => 'literal2',
                    'operator' => ''
                );
            }

            $pattern[] = $entry;
        }

        return $pattern;
    }

    /**
     * Extracts triple pattern out of a given string.
     *
     * @param  string $wherePart WHERE part of the query.
     * @return array
     * @todo   simplify function
     */
    public function extractTriplePattern($wherePart)
    {
        $pattern = array();

        preg_match_all(
            '/' .
            /**
             * Subject part
             */
            '(' .
            '\<[a-zA-Z0-9\.\/\:#\-]+\>|' .  // e.g. <http://foobar/a>
            '\?[a-zA-Z0-9\_]+' .            // e.g. ?s
            ')\s*' .
            /**
             * Predicate part
             */
            '(' .
            '\<[a-zA-Z0-9\.\/\:#\-]+\>|' .  // e.g. <http://foobar/a>
            '\?[a-zA-Z0-9\_]+' .            // e.g. ?s
            ')\s*' .
            /**
             * Object part
             */
            '(' .
            '\<[a-zA-Z0-9\.\/\:#\-]+\>|' .          // e.g. <http://foobar/a>
            '\?[a-zA-Z0-9\_]+|' .                   // e.g. ?s
            '\".*\"[\s|\.|\}]|' .                   // e.g. "Foo"
            '\".*\"\^\^\<[a-zA-Z0-9\.\/\:#]+\>|' .  // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            '\".*\"\@[a-zA-Z\-]{2,}' .              // e.g. "Foo"@en
            ')' .
            '/',
            $wherePart,
            $matches
        );

        $lineIndex = 0;
        
        foreach ($matches[0] as $match) {
            /**
             * Handle type and value of subject and predicate
             */
            $s = str_replace(array('?', '>', '<'), '', $matches[1][$lineIndex]);
            $p = str_replace(array('?', '>', '<'), '', $matches[2][$lineIndex]);
            $o = $matches[3][$lineIndex];
            
            /**
             * Determine types of subject, predicate and object
             */
            $sType = $this->determineEntityType($s);
            $pType = $this->determineEntityType($p);
            $oType = $this->determineEntityType($o);
            
            /**
             * Determine all aspects of the object
             */
            $oDatatype = $this->determineObjectDatatype($o);
            $oLang = $this->determineObjectLanguage($o);
            $oValue = $this->determineObjectValue($o);

            // set pattern array
            $pattern[] = array(
                'type'          => 'triple',
                's'             => $s,
                'p'             => $p,
                'o'             => $oValue,
                's_type'        => $sType,
                'p_type'        => $pType,
                'o_type'        => $oType,
                'o_datatype'    => $oDatatype,
                'o_lang'        => null == $oLang ? '' : $oLang
            );

            ++$lineIndex;
        }

        return $pattern;
    }

    /**
     * Extracts prefixes array from a given query.
     *
     * @param  string $query Query to get prefixes from.
     * @return array List of extracted prefixes.
     */
    public function extractPrefixesFromQuery($query)
    {
        preg_match_all('/\<([a-zA-Z\\\.\/\-\#\:0-9]+)\>/', $query, $matches);

        $uris = array();

        // only use URI until the last occurence of one of these chars: # /
        foreach ($matches[1] as $match) {
            $hashPos = strrpos($match, "#");

            // check for last #
            if (false !== $hashPos) {
                $uri = substr($match, 0, $hashPos + 1);

            } else {
                $slashPos = strrpos($match, '/');

                // check for last /
                if (false !== $slashPos) {
                    $uri = substr($match, 0, $slashPos + 1);

                } else {
                    // TODO: handle case when either # or / was not found
                    continue;
                }
            }

            $uris[$uri] = $uri;
        }

        $uris = array_values($uris);
        $prefixes = array();
        $uriSet = false;
        $prefixNumber = 0;

        foreach ($uris as $uri) {
            $uriSet = false;
            // go through common namespaces and try to find according prefix for
            // current URI
            foreach ($this->namespaces as $prefix => $_uri) {
                if ($uri == $_uri) {
                    $prefixes[$prefix . ':'] = $uri;
                    $uriSet = true;
                    break;
                }
            }
            // in case, it couldnt find according prefix, generate one
            if (false === $uriSet) {
                $prefixes['ns-'. $prefixNumber++ .':'] = $uri;
            }
        }

        return $prefixes;
    }

    /**
     * Extracts type from query.
     *
     * @param  string $query Query to extract type from.
     * @return string|null Returns type or null, if type could not be determined.
     * @throw
     */
    public function extractQueryType($query)
    {
        $query = trim($query);

        $askPosition = strpos($query, 'ASK ');
        $constructPosition = strpos($query, 'CONSTRUCT ');
        $describePosition = strpos($query, 'DESCRIBE ');
        $selectPosition = strpos($query, 'SELECT ');

        // ASK
        if (false !== $askPosition) {
            return 'ask';
            // CONSTRUCT
        } elseif (false !== $constructPosition) {
            return 'construct';
            // DESCRIBE
        } elseif (false !== $describePosition) {
            return 'describe';
            // SELECT
        } elseif (false !== $selectPosition) {
            return 'select';

            // Unknown type
        } else {
            return null;
        }
    }

    /**
     * Extracts result variables from query.
     *
     * @param  array $queryVariables
     * @return array Array of array with variable name and var as type.
     */
    public function extractResultVariablesFromQuery(array $queryVariables)
    {
        $resultVariables = array();

        foreach ($queryVariables as $variable) {
            $resultVariables[] = array(
                'type' => 'var',
                'value' => $variable,
            );
        }

        return $resultVariables;
    }

    /**
     * Extracts variables array from a given query.
     *
     * @param  string $query Query to get prefixes from.
     * @return array List of extracted variables.
     */
    public function extractVariablesFromQuery($query)
    {
        preg_match_all('/\?([a-z]+)/', $query, $matches);

        // TOD handle case nothing was found
        // TOD handle case only * is in SELECT part => extract WHERE part

        return array_values(array_unique($matches[1]));
    }

    /**
     * @var string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @var string
     */
    public function getFromNamed()
    {
        return $this->fromNamed;
    }

    /**
     * @var string
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @var string
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * @var string
     */
    public function getProloguePart()
    {
        return $this->prologuePart;
    }

    /**
     * Synonym for getProloguePart
     *
     * @var string
     */
    public function getSelect()
    {
        return $this->getProloguePart();
    }

    /**
     * @var string
     */
    public function getWhere()
    {
        return $this->wherePart;
    }

    /**
     * Returns used triple pattern in the WHERE clause.
     *
     * @return array $queryParts Triples and graphs
    */
    public function getTriplePatterns()
    {
        $queryParts = $this->getQueryParts();
        $result = array();

        foreach ($queryParts["query"]["pattern"]["patterns"] as $triplePattern) {
            // only use real triple pattern, not filter or something else
            if ("triples" === $triplePattern["type"]) {
                foreach ($triplePattern["patterns"] as $pattern) {
                    if ("triple" === $pattern["type"]) {
                        $result[] = $pattern;
                    }
                }
            }
        }

        return $result;
    }
    
    /**
     * Determines type of the internal query.
     *
     * @return string Returns either ask, select, describe, insertInto, insertData, delete, deleteData.
     */
    public function getType()
    {
        if (null === $this->type) {
            // ADD GRAPH query
            if (false !== strpos($this->getQuery(), 'ADD GRAPH')) {
                $this->type = 'addGraph';
            
            // ASK query
            } elseif (false !== strpos($this->query, 'ASK')) {
                $this->type = 'ask';
            
            // CLEAR GRAPH query
            } elseif (false !== strpos($this->getQuery(), 'CLEAR GRAPH')) {
                $this->type = 'clearGraph';
            
            // DELETE DATA query
            // TODO use prologue part
            } elseif (false !== strpos($this->getQuery(), 'DELETE DATA')) {
                $this->type = 'deleteData';
            
            // DELETE query
            // TODO use prologue part
            } elseif (false !== strpos($this->getQuery(), 'DELETE')) {
                $this->type = 'delete';
            
            // DESCRIBE query
            } elseif (false !== strpos($this->getProloguePart(), 'DESCRIBE DATA')) {
                $this->type = 'describe';
            
            // DROP GRAPH or DROP SILENT GRAPH query
            } elseif (false !== strpos($this->getQuery(), 'DROP GRAPH')
                || false !== strpos($this->getQuery(), 'DROP SILENT GRAPH')) {
                $this->type = 'dropGraph';
                
            // INSERT DATA query
            // TODO use prologue part
            } elseif (false !== strpos($this->getQuery(), 'INSERT DATA')) {
                $this->type = 'insertData';
            
            // INSERT INTO query
            // TODO use prologue part
            } elseif (false !== strpos($this->getQuery(), 'INSERT INTO')) {
                $this->type = 'insertInto';
                
            // SELECT query
            } elseif (false !== strpos($this->getProloguePart(), 'SELECT')) {
                $this->type = 'select';
            }
        }
        
        return $this->type;
    }
    
    /**
     * Returns raw query, if available.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Parsing the given queryand extract its parts.
     *
     * @return array $queryParts Triples and graphs
    */
    public function getQueryParts()
    {
        if (null === $this->queryParts) {
            $this->queryParts = array(
                'query' => array(
                    'dataset' => $this->extractDatasetsFromQuery(
                        $this->getFrom(),
                        $this->getFromNamed()
                    ),
                    'limit' => $this->getLimit(),
                    'offset' => $this->getOffset(),
                    'pattern' => array(
                        'patterns' => array(),
                        // TODO extend type determination (see ARC2)
                        'type' => 'group'
                    ),
                    'result_vars' => $this->extractResultVariablesFromQuery(
                        $this->extractVariablesFromQuery($this->getSelect())
                    ),
                    'type' => $this->extractQueryType($this->query),
                ),
                'prefixes' => $this->extractPrefixesFromQuery($this->getWhere()),
                'vars' => $this->extractVariablesFromQuery($this->query)
            );

            // add triple patterns
            $this->queryParts['query']['pattern']['patterns'][] = array(
                'type' => 'triples',
                'patterns' => $this->extractTriplePattern($this->getWhere()),
            );

            // add filter, if available
            $this->queryParts['query']['pattern']['patterns'] = array_merge(
                $this->queryParts['query']['pattern']['patterns'],
                $this->extractFilterPattern($this->getWhere())
            );
        }

        return $this->queryParts;
    }

    /**
     * Init Query instance with a SPARQL query string. This function tries to parse the query and use as much
     * information as possible. But unfortunatly not every SPARQL 1.0/1.1 aspect is supported.
     *
     * @param string $queryString
     * @throws \Exception If $queryString is empty.
     */
    public function init($queryString)
    {
        if (true === empty($queryString) || null === $queryString) {
            throw new \Exception('Parameter $queryString is empty or null.');
        }
        
        $this->query = $queryString;

        $parts = array(
            'prologue'   => array(),
            'from'       => array(),
            'from_named' => array(),
            'where'      => array(),
            'order'      => array(),
            'limit'      => array(),
            'offset'     => array()
        );

        $var = '[?$]{1}[\w\d]+';
        
        // TODO support INSERT DATA queries
        // TODO support INSERT INTO queries
        // TODO support DELETE DATA queries
        // TODO support DELETE queries
        // TODO support ADD GRAPH
        // TODO support CLEAR GRAPH
        // TODO support DROP GRAPH
        $tokens = array(
            'prologue'   => '/(' .

                            // BASE and PREFIX part
                            '(BASE.*\s)?(PREFIX.*?\s)*('.
                            
                            // ASK query; matches queries like ASK { ?s ?p ?o }
                            'ASK\s*\{(.*)\}|'.
                            
                            // SELECT part
                            '((SELECT(\s)+)(DISTINCT(\s)+)'.

                            // COUNT
                            '?(COUNT(\s)*(\(.*?\)(\s)))?)(\?\w+\s+|\*)*'.

                            // LANG
                            '(\(LANG\(\?[a-zA-Z0-9\_]+\)\)* as{1}\s\?[a-zA-Z0-9\_]+)*'.

                            '))/si',
            'from'       => '/FROM\s+<(.+?)>/i',
            'from_named' => '/FROM\s+NAMED\s+<(.+?)>/i',
            'where'      => '/(WHERE\s+)?\{.*\}/si',
            'order'      => '/ORDER\s+BY((\s+' . $var . '|\s+(ASC|DESC)\s*\(\s*' . $var . '\s*\))+)/i',
            'limit'      => '/LIMIT\s+(\d+)/i',
            'offset'     => '/OFFSET\s+(\d+)/i'
        );

        foreach ($tokens as $key => $pattern) {
            preg_match_all($pattern, $queryString, $parts[$key]);
        }

        if (isset($parts['prologue'][0][0])) {
            // ASK query match is on another position
            if (false !== strpos($parts['prologue'][0][0], 'ASK')) {
                $this->setProloguePart(trim($parts['prologue'][5][0]) . ' ');
            
            // for all other queries
            } else {
                $this->setProloguePart(trim($parts['prologue'][0][0]) . ' ');
            }
        }

        if (isset($parts['from'][1][0])) {
            $this->setFrom($parts['from'][1]);
        }

        if (isset($parts['from_named'][1][0])) {
            $this->setFromNamed($parts['from_named'][1]);
        }

        if (isset($parts['where'][0][0])) {
            $this->setWherePart($parts['where'][0][0]);
        }

        if (isset($parts['order'][1][0])) {
            $this->setOrderClause(trim($parts['order'][1][0]));
        }

        if (isset($parts['limit'][1][0])) {
            $this->setLimit($parts['limit'][1][0]);
        }

        if (isset($parts['offset'][1][0])) {
            $this->setOffset($parts['offset'][1][0]);
        }
        
        // call function to set type
        $this->getType();
    }

    public function resetInstance()
    {
        $this->prologuePart = null;
        $this->from         = array();
        $this->fromNamed    = array();
        $this->wherePart    = null;
        $this->orderClause  = null;
        $this->limit        = null;
        $this->offset       = null;

        return $this;
    }

    public function setFrom(array $newFromArray)
    {
        $this->from = $newFromArray;

        return $this;
    }

    public function setFromNamed(array $newFromNamedArray)
    {
        $this->fromNamed = $newFromNamedArray;

        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function setOrderClause($orderString)
    {
        $this->orderClause = $orderString;

        return $this;
    }

    public function setProloguePart($prologueString)
    {
        $this->prologuePart = $prologueString;

        return $this;
    }

    public function setWherePart($whereString)
    {
        if (stripos($whereString, 'where') !== false) {
            $this->wherePart = $whereString;
        } else {
            $this->wherePart = 'WHERE' . $whereString;
        }

        return $this;
    }
}
