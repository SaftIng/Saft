<?php

namespace Saft\Sparql\Query;

use Saft\Rdf\NodeUtils;

/**
 * Represents a SPARQL query.
 */
abstract class AbstractQuery implements Query
{
    /**
     * List of widely used namespaces.
     *
     * @var array
     */
    protected $commonNamespaces = array(
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
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $queryParts = array();

    /**
     * Constructor.
     *
     * @param  string     optional $query SPARQL query string to initialize this instance.
     * @throws \Exception If parameter $query is empty, null or not a string.
     */
    public function __construct($query = '')
    {
        if (true === empty($query) || null === $query || false === is_string($query)) {
            return;
        }

        $this->query = $query;
    }

    /**
     * Determines type of a entity.
     *
     * @param  string $entity Entity string.
     * @return string|null    Returns either literal, typed-literal, uri or var. Returns null if it couldnt be
     *                        determined.
     */
    public function determineEntityType($entity)
    {
        // remove braces at the beginning (only if $entity looks like <http://...>)
        if ('<' == substr($entity, 0, 1)) {
            $entity = str_replace(array('>', '<'), '', $entity);
        }

        // checks if $entity is an URL
        if (true === NodeUtils::simpleCheckURI($entity)) {
            return 'uri';

        // checks if ^^< is in $entity OR if $entity is surrounded by quotation marks
        } elseif (false !== strpos($entity, '"^^<')
            || ('"' == substr($entity, 0, 1)
                && '"' == substr($entity, strlen($entity)-1, 1))) {
            return 'typed-literal';

        // checks if $entity is an URL, which was written with prefix, such as rdfs:label
        } elseif (false !== strpos($entity, ':')) {
            return 'uri';

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
     * Extracts filter pattern out of a given string.
     *
     * @param  string $where WHERE part of the query.
     * @return array
     * TODO   simplify function
     * TODO   extend support for further filters
     */
    public function extractFilterPattern($where)
    {
        /**
         * Remaining filter clauses to cover:
           - FILTER (?decimal * 10 > ?minPercent )
           - FILTER (isURI(?person) && !bound(?person))
           - FILTER (lang(?title) = 'en')
           - FILTER regex(?ssn, '...')
        */

        $pattern = array();

        preg_match_all(
            '/FILTER(\s*|\s*[a-zA-Z0-9]*)\((.*)\)/',
            $where,
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
                $entry = array(
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
                );

                // set datatype and sub_type
                if (true === ctype_digit($parts[3][0])) {
                    $entry['patterns'][1]['datatype'] =
                        'http://www.w3.org/2001/XMLSchema#integer';
                } else {
                    $entry['patterns'][1]['sub_type'] = 'literal2';
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
            $where,
            $matches
        );

        if (true == isset($matches[1][0])) {
            $entry = array(
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
            );

            // if optional part is set, which means the regex function gots 3 parameter
            if (true === isset($matches[4][0])) {
                $entry['args'][] = array(
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
     * Extracts graph(s) from queryPart.
     *
     * @param  string $queryPart SPARQL query part.
     * @return array
     */
    public function extractGraphs($queryPart)
    {
        $graphs = array();

        $result = preg_match_all('/\FROM\s*\<(.*)\\>/', $queryPart, $matches);

        if (false !== $result && true === isset($matches[1][0])) {
            $graphs[] = $matches[1][0];
        }

        return $graphs;
    }

    /**
     * Extracts named graph(s) from queryPart.
     *
     * @param  string $queryPart SPARQL query part.
     * @return array
     */
    public function extractNamedGraphs($queryPart)
    {
        $graphs = array();

        $result = preg_match_all('/\FROM NAMED\s*\<(.*)\\>/', $queryPart, $matches);

        if (false !== $result && true === isset($matches[1][0])) {
            $graphs[] = $matches[1][0];
        }

        return $graphs;
    }

    /**
     * Extracts prefixes from a given query.
     *
     * @param  string $query Query to get prefixes from.
     * @return array         List of extracted prefixes.
     */
    public function extractNamespacesFromQuery($query)
    {
        preg_match_all('/\<([a-zA-Z\\\.\/\-\#\:0-9]+)\>/', $query, $matches);

        $uris = array();

        // only use URI until the last occurence of one of these chars: # /
        foreach ($matches[1] as $match) {
            $hashPos = strrpos($match, '#');

            // check for last #
            if (false !== $hashPos) {
                $uri = substr($match, 0, $hashPos + 1);

            } else {
                if (7 < strlen($match)) {
                    $slashPos = strrpos($match, '/', 7);

                    // check for last /
                    if (false !== $slashPos) {
                        $uri = substr($match, 0, $slashPos + 1);
                    } else {
                        continue;
                    }
                } else {
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
            foreach ($this->commonNamespaces as $prefix => $_uri) {
                if ($uri == $_uri) {
                    $prefixes[$prefix] = $uri;
                    $uriSet = true;
                    break;
                }
            }
            // in case, it couldnt find according prefix, generate one
            if (false === $uriSet) {
                $prefixes['ns-'. $prefixNumber++] = $uri;
            }
        }

        return $prefixes;
    }

    /**
     * Extracts prefixes from the prologue part of the given query.
     *
     * @param  string $query Query to get prologue prefixes from.
     * @return array         List of extracted prefixes.
     */
    public function extractPrefixesFromQuery($query)
    {
        preg_match_all('/PREFIX\s+([a-z0-9]+)\:\s*\<([a-z0-9\:\/\.\#\-]+)\>/', $query, $matches);

        $prefixes = array();

        foreach ($matches[1] as $index => $key) {
            $prefixes[$key] = $matches[2][$index];
        }

        return $prefixes;
    }

    /**
     * Extracts quads from query, if available.
     *
     * @param string $query
     * @return array
     */
    public function extractQuads($query)
    {
        $quads = array();

        /**
         * Matches the following pattern: Graph <http://uri/> { ?s ?p ?o }
         * Whereas ?s ?p ?o stands for any triple, so also for an URI. It also matches multi line strings
         * which have { and triple on different lines.
         */
        $result = preg_match_all('/GRAPH\s*\<(.*)\>\s*\{\n*(.*)\s*\n*\}/mi', $query, $matches);

        // if no errors occour and graphs and triple where found
        if (false !== $result
            && true === isset($matches[1])
            && true === isset($matches[2])) {
            foreach ($matches[1] as $key => $graph) {
                // parse according triple string, for instance: <http://saft/test/s1> dc:p1 <http://saft/test/o1>
                // and extract S, P and O.
                $triplePattern = $this->extractTriplePattern($matches[2][$key]);

                // TODO Handle case that more than one triple pattern was found

                if (0 == count($triplePattern)) {
                    throw new \Exception('Quad related part of the query is invalid: '. $matches[2][$key]);
                }

                $quad = $triplePattern[0];
                $quad['g'] = $graph;
                $quad['g_type'] = 'uri';

                $quads[] = $quad;
            }
        }

        return $quads;
    }

    /**
     * Extracts triple pattern out of a given string.
     *
     * @param  string $where WHERE part of the query.
     * @return array
     */
    public function extractTriplePattern($where)
    {
        /**
         * Because quads also consists of triple pattern, we first check if there are quads. In case there
         * are, return empty.
         */
        $quads = $this->extractQuads($where);

        if (0 < count($quads)) {
            return array();
        }

        /**
         * Here we know, no quads are there.
         */
        $pattern = array();

        preg_match_all(
            '/' .
            /**
             * Subject part
             */
            '(' .
            '\<[a-z0-9\.\/\:#\-]+\>|' . // e.g. <http://foobar/a>
            '\?[a-z0-9\_]+|' .          // e.g. ?s
            '[a-z0-9]+\:[a-z0-9]+' .    // e.g. rdfs:label
            ')\s*' .
            /**
             * Predicate part
             */
            '(' .
            '\<[a-z0-9\.\/\:#\-]+\>|' . // e.g. <http://foobar/a>
            '\?[a-z0-9\_]+|' .          // e.g. ?s
            '[a-z0-9]+\:[a-z0-9]+' .    // e.g. rdfs:label
            ')\s*' .
            /**
             * Object part
             */
            '(' .
            '\<[a-zA-Z0-9\.\/\:#\-]+\>|' .      // e.g. <http://foobar/a>
            '[a-z0-9]+\:[a-z0-9]+|' .           // e.g. rdfs:label
            '\?[a-z0-9\_]+|' .                  // e.g. ?s
            '\".*\"[\s|\.|\}]|' .               // e.g. "Foo"
            '\".*\"\^\^\<[a-z0-9\.\/\:#]+\>|' . // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            '\".*\"\@[a-z\-]{2,}' .             // e.g. "Foo"@en
            ')' .
            '/im',
            $where,
            $matches
        );

        $lineIndex = 0;

        foreach ($matches[0] as $match) {
            /**
             * Handle type and value of subject and predicate
             */
            $s = $matches[1][$lineIndex];
            $p = $matches[2][$lineIndex];
            $o = $matches[3][$lineIndex];

            $oIsUri = false;
            if ('<' == substr($o, 0, 1)) {
                $o = str_replace(array('>', '<'), '', $o);
                $oIsUri = true;
            }

            /**
             * Determine types of subject, predicate and object
             */
            $sType = $this->determineEntityType($s);
            $pType = $this->determineEntityType($p);
            $oType = $this->determineEntityType($o);

            /**
             * Check for prefixes and replace them with original URI
             */
            $prefixes = $this->extractPrefixesFromQuery($this->getQuery());

            $s = $this->replacePrefixWithUri($s, $prefixes);
            $p = $this->replacePrefixWithUri($p, $prefixes);
            $o = $this->replacePrefixWithUri($o, $prefixes);

            /**
             * If S or P are starting with a ?, remove it
             */
            if ('?' == substr($s, 0, 1)) {
                $s = substr($s, 1);
            }

            if ('?' == substr($p, 0, 1)) {
                $p = substr($p, 1);
            }

            /**
             * Determine all aspects of the object
             */
            $oDatatype = $this->determineObjectDatatype($o);
            $oLang = $this->determineObjectLanguage($o);
            $oValue = $this->determineObjectValue($o);

            if ($oIsUri === true) {
                $oValue = $o;
            }

            // set pattern array
            $pattern[] = array(
                's'             => str_replace(array('<', '>'), '', $s),
                'p'             => str_replace(array('<', '>'), '', $p),
                'o'             => $oValue,
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
     * Extracts variables array from a given query.
     *
     * @param  string $query Query to get prefixes from.
     * @return array List of extracted variables.
     */
    public function extractVariablesFromQuery($query)
    {
        preg_match_all('/\?([a-z]+)/', $query, $matches);

        // TOD handle case nothing was found

        return array_values(array_unique($matches[1]));
    }

    /**
     * Returns a list of common namespaces.
     *
     * @return array
     */
    public function getCommonNamespaces()
    {
        return $this->commonNamespaces;
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
     * Get type for a given query.
     *
     * @param  string     $query
     * @return string     Type, which is either askQuery, describeQuery, graphQuery, updateQuery or selectQuery
     * @throws \Exception If unknown query type.
     */
    public static function getQueryType($query)
    {
        /**
         * First we get rid of all PREFIX information
         */
        $adaptedQuery = preg_replace('/PREFIX\s+[a-z0-9]+\:\s*\<[a-z0-9\:\/\.\#\-]+\>/', '', $query);

        // remove trailing whitespaces
        $adaptedQuery = trim($adaptedQuery);

        // only lower chars
        $adaptedQuery = strtolower($adaptedQuery);

        /**
         * After we know the type, we initiate the according class and return it.
         */
        $firstPart = substr($adaptedQuery, 0, 3);

        switch($firstPart) {
            // ASK
            case 'ask':
                return 'askQuery';

            // DESCRIBE
            case 'des':
                return 'describeQuery';

            /**
             * If we land here, we have to use a higher range of characters
             */
            default:
                $firstPart = substr($adaptedQuery, 0, 6);

                switch($firstPart) {
                    // CLEAR GRAPH
                    case 'clear ':
                        return 'graphQuery';

                    // CREATE GRAPH
                    // CREATE SILENT GRAPH
                    case 'create':
                        return 'graphQuery';

                    // DELETE DATA
                    case 'delete':
                        return 'updateQuery';

                    // DROP GRAPH
                    case 'drop g':
                        return 'graphQuery';

                    // DROP SILENT GRAPH
                    case 'drop s':
                        return 'graphQuery';

                    // INSERT DATA
                    // INSERT INTO
                    case 'insert':
                        return 'updateQuery';

                    // SELECT
                    case 'select':
                        return 'selectQuery';

                    default:

                        // check if query is of type: WITH <http:// ... > DELETE { ... } WHERE { ... }
                        // TODO make it more precise
                        if (false !== strpos($adaptedQuery, 'with')
                            && false !== strpos($adaptedQuery, 'delete')
                            && false !== strpos($adaptedQuery, 'where')) {
                            return 'updateQuery';

                        // check if query is of type: WITH <http:// ... > DELETE { ... }
                        // TODO make it more precise
                        } elseif (false !== strpos($adaptedQuery, 'with')
                            && false !== strpos($adaptedQuery, 'delete')) {
                            return 'updateQuery';
                        }
                }
        }

        throw new \Exception('Unknown query type: '. $firstPart);
    }

    /**
     * Replaces the prefix in a string with the original URI
     *
     * @param  string $prefixedString String to adapt.
     * @param  array  $prefixes       Array containing prefixes as keys and according URI as value.
     * @return string
     */
    public function replacePrefixWithUri($prefixedString, $prefixes)
    {
        // check for qname. a qname was given if a : was found, but no < and >
        if (false !== strpos($prefixedString, ':') &&
            false === strpos($prefixedString, '<') && false === strpos($prefixedString, '>')) {
            // prefix is the part before the :
            $prefix = substr($prefixedString, 0, strpos($prefixedString, ':'));

            // if a prefix
            if (true === isset($prefixes[$prefix])) {
                $prefixedString = str_replace($prefix . ':', $prefixes[$prefix], $prefixedString);
            }
        }

        return $prefixedString;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Unsets values if its is empty
     *
     * @param array &$array Reference to array to adapt.
     */
    public function unsetEmptyValues(&$array)
    {
        foreach ($array as $key => $entry) {
            if (true === empty($entry)) {
                unset($array[$key]);
            }
        }
    }
}
