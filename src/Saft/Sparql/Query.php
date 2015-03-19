<?php

namespace Saft\Sparql;

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
     * @var string
     */
    protected $prologuePart = null;

    /**
     * @var array
     */
    protected $from = array();

    /**
     * @var array
     */
    protected $fromNamed = array();

    /**
     * @var string
     */
    protected $wherePart = null;

    /**
     * @var string
     */
    protected $orderClause = null;

    /**
     * @var int
     */
    protected $limit = null;
    
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
    protected $query = "";

    /**
     * @var array
     */
    protected $queryParts;

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
            '\".*\"|' .                             // e.g. "Foo"
            '\".*\"\^\^\<[a-zA-Z0-9\.\/\:#]+\>|' .  // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            '\".*\"\@[a-zA-Z\-]{2,}' .              // e.g. "Foo"@en
            ')' .
            // final point
            '\s*\.' .
            '/',
            $wherePart,
            $matches
        );

        $lineIndex = 0;

        foreach ($matches[0] as $match) {
            /**
             * remove the following chars: < > ?
             */
            $s = str_replace(array('?', '>', '<'), '', $matches[1][$lineIndex]);
            $p = str_replace(array('?', '>', '<'), '', $matches[2][$lineIndex]);

            // object is a literal (with either langauge or datatype)
            $arrowPos = strpos($matches[3][$lineIndex], '"^^<'); // datatype
            $atPos = strpos($matches[3][$lineIndex], '"@'); // language
            if (false !== $arrowPos) {
                $o = substr($matches[3][$lineIndex], 1, $arrowPos - 1);
            } elseif (false !== $atPos) {
                $o = substr($matches[3][$lineIndex], 1, $atPos - 1);
            } else {
                $o = str_replace(array('?', '>', '<'), '', $matches[3][$lineIndex]);
            }

            /**
             * determine type of subject, predicate and object
             */
            $sType = true === \Saft\Rdf\NamedNodeImpl::check($s) ? 'uri' : 'var';
            $pType = true === \Saft\Rdf\NamedNodeImpl::check($p) ? 'uri' : 'var';
            if (true === \Saft\Rdf\NamedNodeImpl::check($o)) {
                $oType = 'uri';
            } elseif (false !== $arrowPos) {
                $oType = 'typed-literal';
            } elseif (false !== $atPos) {
                $oType = 'literal';
            } else {
                $oType = 'var';
            }

            /**
             * set objects datatype and lang
             */
            if ('typed-literal' == $oType) {
                // save only the datatype URI, e.g. http://www.w3.org/2001/XMLSchema#string
                $oDatatype = str_replace(
                    '>',
                    '',
                    substr($matches[3][$lineIndex], $arrowPos + 4)
                );
                $oLang = '';
            } elseif ('literal' == $oType) {
                $oDatatype = '';
                // save only the name of the language, e.g. en
                $oLang = substr($matches[3][$lineIndex], $atPos + 2);
            } else {
                $oDatatype = '';
                $oLang = '';
            }

            // set pattern array
            $pattern[] = array(
                'type'          => 'triple',
                's'             => $s,
                'p'             => $p,
                'o'             => $o,
                's_type'        => $sType,
                'p_type'        => $pType,
                'o_type'        => $oType,
                'o_datatype'    => $oDatatype,
                'o_lang'        => $oLang
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

    public function getFrom()
    {
        return $this->from;
    }

    public function getFromNamed()
    {
        return $this->fromNamed;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
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
     * @param  string $queryString Query to extract parts from
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
     *
     * @param
     * @return
     * @throw
     */
    public function init($queryString)
    {
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
        $tokens = array(
            'prologue'   => '/(' .

                            // BASE PREFIX ASK SELECT DISTINCT
                            '(BASE.*\s)?(PREFIX.*?\s)*(ASK|((SELECT(\s)+)(DISTINCT(\s)+)'.

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
            $this->setProloguePart(trim($parts['prologue'][0][0]) . ' ');   // whole match
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
    }
    
    /**
     * Checks if query is an ASK query.
     *
     * @return boolean
     */
    public function isAskQuery()
    {
        return false !== strpos($this->getProloguePart(), 'ASK');
    }
    
    /**
     * Checks if query is a DELETE query.
     *
     * @return boolean
     */
    public function isDeleteQuery()
    {
        return false !== strpos($this->getProloguePart(), 'DELETE');
    }
    
    /**
     * Checks if query is an INSERT query.
     *
     * @return boolean
     */
    public function isInsertQuery()
    {
        return false !== strpos($this->getProloguePart(), 'INSERT');
    }
    
    /**
     * Checks if query is SPARQL UPDATE by checking if its either an insert- or delete query.
     *
     * @return boolean
     */
    public function isUpdateQuery()
    {
        return $this->isInsertQuery() || $this->isDeleteQuery();
    }

    public function getProloguePart()
    {
        return $this->prologuePart;
    }

    /**
     * Synonym for getProloguePart
     */
    public function getSelect()
    {
        return $this->getProloguePart();
    }

    /**
     *
     */
    public function getWhere()
    {
        return $this->wherePart;
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
