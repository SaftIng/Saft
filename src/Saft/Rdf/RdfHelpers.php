<?php

namespace Saft\Rdf;

/**
 * Class which provides useful methods for RDF related operations, for instance node creation or
 * URI checks.
 *
 * @api
 * @package Saft\Rdf
 * @since 0.9
 */
class RdfHelpers
{
    /**
     * @param StatementIteratorFactory $statementIteratorFactory optional, if not set Impl variant will be used.
     */
    public function __construct(StatementIteratorFactory $statementIteratorFactory = null)
    {
        if (null == $statementIteratorFactory) {
            $this->statementIteratorFactory = new StatementIteratorFactoryImpl();
        } else {
            $this->statementIteratorFactory = $statementIteratorFactory;
        }
    }

    /**
     * @param string $s
     * @return string encoded string for n-quads
     */
    public function encodeStringLitralForNQuads($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace("\t", '\t', $s);
        $s = str_replace("\n", '\n', $s);
        $s = str_replace("\r", '\r', $s);
        $s = str_replace('"', '\"', $s);

        return $s;
    }

    /**
     * Returns given Node instance in SPARQL format, which is in NQuads or as Variable
     *
     * @param Node $node Node instance to format.
     * @param string $var The variablename, which should be used, if the node is not concrete
     * @return string Either NQuad notation (if node is concrete) or as variable.
     */
    public function getNodeInSparqlFormat(Node $node, $var = null)
    {
        if ($node->isConcrete()) {
            return $node->toNQuads();
        }
        return '?' . uniqid('tempVar');
    }

    /**
     * Get type for a given SPARQL query.
     *
     * @param  string $query
     * @return string Type, which is either askQuery, describeQuery, graphQuery, updateQuery or selectQuery
     * @throws \Exception if unknown query type.
     */
    public function getQueryType($query)
    {
        /**
         * First we get rid of all PREFIX information
         */
        $adaptedQuery = preg_replace('/PREFIX\s+[a-z0-9\-]+\:\s*\<[a-z0-9\:\/\.\#\-\~\_]+\>/si', '', $query);

        // remove whitespace lines and trailing whitespaces
        $adaptedQuery = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", trim($adaptedQuery));

        // only lower chars
        $adaptedQuery = strtolower($adaptedQuery);

        /**
         * After we know the type, we initiate the according class and return it.
         */
        $firstPart = substr($adaptedQuery, 0, 3);

        switch ($firstPart) {
            // ASK
            case 'ask':
                return 'askQuery';

            // CONSTRUCT
            case 'con':
                return 'constructQuery';

            // DESCRIBE
            case 'des':
                return 'describeQuery';

            /**
             * If we land here, we have to use a higher range of characters
             */
            default:
                $firstPart = substr($adaptedQuery, 0, 6);

                switch ($firstPart) {
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

        throw new \Exception('Unknown query type "'. $firstPart .'" for query: '. $adaptedQuery);
    }

    /**
     * Returns the regex string to get a node from a triple/quad.
     *
     * @param boolean $useVariables optional, default is false
     * @param boolean $useNamespacedUri optional, default is false
     * @return string
     */
    public function getRegexStringForNodeRecognition(
        $useBlankNode = false,
        $useNamespacedUri = false,
        $useTypedString = false,
        $useLanguagedString = false,
        $useSimpleString = false,
        $useSimpleNumber = false,
        $useVariables = false
    ) {
        $regex = '(<([a-z]{2,}:[^\s]*)>)'; // e.g. <http://foobar/a>

        if (true == $useBlankNode) {
            $regex .= '|(_:([a-z0-9A-Z_]+))'; // e.g. _:foobar
        }

        if (true == $useNamespacedUri) {
            $regex .= '|(([a-z0-9]+)\:([a-z0-9]+))'; // e.g. rdfs:label
        }

        if (true == $useTypedString) {
            // e.g. "Foo"^^<http://www.w3.org/2001/XMLSchema#string>
            $regex .= '|(\"(.*?)\"\^\^\<([^\s]+)\>)';
        }

        if (true == $useLanguagedString) {
            $regex .= '|(\"(.*?)\"\@([a-z\-]{2,}))'; // e.g. "Foo"@en
        }

        if (true == $useSimpleString) {
            $regex .= '|(\"(.*?)\")'; // e.g. "Foo"
        }

        if (true == $useSimpleNumber) {
            $regex .= '|([0-9]{1,})'; // e.g. 42
        }

        if (true == $useVariables) {
            $regex .= '|(\?[a-z0-9\_]+)'; // e.g. ?s
        }

        return $regex;
    }

    /**
     * @param string $stringToCheck
     * @return null|string
     */
    public function guessFormat($stringToCheck)
    {
        if (false == is_string($stringToCheck)) {
            throw new \Exception('Invalid $stringToCheck value given. It needs to be a string.');
        }

        $short = substr($stringToCheck, 0, 1024);

        // n-triples/n-quads
        if (0 < preg_match('/^<.+>/m', $short, $matches)) {
            return 'n-triples';

        // RDF/XML
        } elseif (0 < preg_match('/<rdf:/i', $short, $matches)) {
            return 'rdf-xml';
        }

        return null;
    }

    /**
     * Checks if a given string is a blank node ID. Blank nodes are usually structured like
     * _:foo, whereas _: comes first always.
     *
     * @param string $string String to check if its a blank node ID or not.
     * @return boolean True if given string is a valid blank node ID, false otherwise.
     */
    public function simpleCheckBlankNodeId($string)
    {
        return '_:' == substr($string, 0, 2);
    }

    /**
     * Checks the general syntax of a given URI. Protocol-specific syntaxes are not checked. Instead, only
     * characters disallowed an all URIs lead to a rejection of the check. Use this function, if you need a
     * basic check and if performance is an issuse. In case you need a more precise check, that function is
     * not recommended.
     *
     * @param string $string String to check if its a URI or not.
     * @return boolean True if given string is a valid URI, false otherwise.
     * @api
     * @since 0.1
     */
    public function simpleCheckURI($string)
    {
        $regEx = '/^([a-z]{2,}:[^\s]*)$/';
        return (1 === preg_match($regEx, (string)$string));
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param StatementIterator|array $statements List of statements to format as SPARQL string.
     * @param Node $graph Use if each statement is a triple and to use another graph as the default.
     * @return string Statement data in SPARQL format
     */
    public function statementIteratorToSparqlFormat($statements, Node $graph = null)
    {
        $query = '';
        foreach ($statements as $statement) {
            if ($statement instanceof Statement) {
                $con = $this->getNodeInSparqlFormat($statement->getSubject()) . ' ' .
                       $this->getNodeInSparqlFormat($statement->getPredicate()) . ' ' .
                       $this->getNodeInSparqlFormat($statement->getObject()) . ' . ';

                $graphToUse = $graph;
                if ($graph == null && $statement->isQuad()) {
                    $graphToUse = $statement->getGraph();
                }

                if (null !== $graphToUse) {
                    $sparqlString = 'Graph '. self::getNodeInSparqlFormat($graphToUse) .' {' . $con .'}';
                } else {
                    $sparqlString = $con;
                }

                $query .= $sparqlString .' ';
            } else {
                throw new \Exception('Not a Statement instance');
            }
        }
        return $query;
    }

    /**
     * Returns the Statement-Data in sparql-Format.
     *
     * @param array $statements List of statements to format as SPARQL string.
     * @param string $graphUri Use if each statement is a triple and to use another graph as
     *                         the default. (optional)
     * @return string Statement data in SPARQL format
     */
    public function statementsToSparqlFormat(array $statements, Node $graph = null)
    {
        return $this->statementIteratorToSparqlFormat(
            $this->statementIteratorFactory->createStatementIteratorFromArray($statements),
            $graph
        );
    }
}
