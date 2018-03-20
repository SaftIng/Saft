<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Sparql\Query;

use Saft\Rdf\RdfHelpers;

/**
 * Represents the following types of SPARQL queries:
 * - INSERT DATA
 * - INSERT INTO GRAPH
 * - DELETE DATA
 * - DELETE WHERE
 * - WITH ... DELETE ... WHERE
 * - WITH ... DELETE ... INSERT ... WHERE.
 */
class UpdateQueryImpl extends AbstractQuery
{
    /**
     * Constructor.
     *
     * @param string     $query      SPARQL query string to initialize this instance
     * @param RdfHelpers $rdfHelpers
     *
     * @throws \Exception if no where part was found in query
     * @throws \Exception if given query is not suitable for UpdateQuery
     * @throws \Exception if no triple part after INSERT DATA found
     * @throws \Exception if no triple part after DELETE DATA found
     * @throws \Exception If no valid WITH <> DELETE {...} WHERE { ...} query given.
     * @throws \Exception If no valid WITH <> DELETE {...} INSERT { ... } WHERE { ...} query given.
     * @throws \Exception if there is either no triple part after INSERT INTO GRAPH or no graph set
     */
    public function __construct($query = '', RdfHelpers $rdfHelpers)
    {
        parent::__construct($query, $rdfHelpers);

        if (null == $this->query) {
            return;
        }

        $subType = $this->getSubType();

        if (null !== $subType) {
            /*
             * Save parts for INSERT DATA
             */
            if ('insertData' === $subType) {
                preg_match('/INSERT\s+DATA\s+\{(.*)\}/si', $query, $matches);

                if (false === isset($matches[1])) {
                    throw new \Exception('No triple part after INSERT DATA found.');
                }

                /*
                 * Save parts for INSERT INTO GRAPH <> {} or INSERT INTO <> {}
                 */
            } elseif ('insertInto' === $subType) {
                preg_match('/INSERT\s+INTO\s+[GRAPH]{0,}\s*\<(.*)\>\s*\{(.*)\}/si', $query, $matches);

                if (false === isset($matches[1]) || false === isset($matches[2])) {
                    throw new \Exception(
                        'There is either no triple part after INSERT INTO GRAPH or no graph set.'
                    );
                }

                /*
                 * Save parts for DELETE DATA {}
                 */
            } elseif ('deleteData' === $subType) {
                preg_match('/DELETE\s+DATA\s*\{(.*)\}/si', $query, $matches);

                if (false === isset($matches[1])) {
                    throw new \Exception('No triple part after DELETE DATA found.');
                }

                /*
                 * Save parts for WITH <> DELETE {} WHERE {}
                 */
            } elseif ('withDeleteWhere' === $subType) {
                preg_match('/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/si', $query, $matches);

                if (false === isset($matches[1])) {
                    throw new \Exception(
                        'No valid WITH <> DELETE {...} WHERE { ...} query given.'
                    );
                }

                /*
                 * Save parts for WITH <> DELETE {} INSERT {} WHERE {}
                 */
            } elseif ('withDeleteWhere' === $subType) {
                preg_match(
                    '/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*INSERT\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/si',
                    $query,
                    $matches
                );

                if (false === isset($matches[1])) {
                    throw new \Exception(
                        'No valid WITH <> DELETE {...} INSERT { ... } WHERE { ...} query given.'
                    );
                }
            }
        } else {
            throw new \Exception('Given query is not suitable for UpdateQuery: '.$query);
        }
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function extractGraphs($query)
    {
        $graphs = [];

        /**
         * Matches the following pattern: Graph <http://uri/>.
         */
        $result = preg_match_all('/GRAPH\s*\<([a-z0-9\:\/]+)\>/si', $query, $matches);

        if (false !== $result && true === isset($matches[1])) {
            foreach ($matches[1] as $graph) {
                $graphs[] = $graph;
            }
        }

        return $graphs;
    }

    /**
     * @return string|null
     */
    public function getSubType()
    {
        /**
         * First we get rid of all PREFIX information.
         */
        $adaptedQuery = preg_replace('/PREFIX\s+[a-z0-9]+\:\s*\<[a-z0-9\:\/\.\#\-]+\>/si', '', $this->getQuery());

        // remove trailing whitespaces
        $adaptedQuery = trim($adaptedQuery);

        // only lower chars
        $adaptedQuery = strtolower($adaptedQuery);

        // only first part and without whitespaces
        $firstPart = str_replace(' ', '', substr($adaptedQuery, 0, 8));

        // TODO make check more precise, because its possible that are more than one whitespace between keywords.
        switch ($firstPart) {
            // DELETE DATA
            case 'deleted':
                return 'deleteData';

            // DELETE FROM <> { } WHERE { } (SPARQL+ from ARC2)
            case 'deletef':
                return 'deleteFromWhere';

            // DELETE WHERE
            case 'deletew':
                return 'deleteWhere';

            // INSERT DATA
            case 'insertd':
                return 'insertData';

            // INSERT INTO
            case 'inserti':
                return 'insertInto';

            default:
                // check if query is of type: WITH <http:// ... > DELETE { ... } INSERT { ... } WHERE { ... }
                // TODO make it more precise
                if (false !== strpos($adaptedQuery, 'with')
                    && false !== strpos($adaptedQuery, 'delete')
                    && false !== strpos($adaptedQuery, 'insert')
                    && false !== strpos($adaptedQuery, 'where')) {
                    return 'withDeleteInsertWhere';

                // check if query is of type: WITH <http:// ... > DELETE { ... } WHERE { ... }
                // TODO make it more precise
                } elseif (false !== strpos($adaptedQuery, 'with')
                    && false !== strpos($adaptedQuery, 'delete')
                    && false !== strpos($adaptedQuery, 'where')) {
                    return 'withDeleteWhere';

                // check if query is of type: WITH <http:// ... > DELETE { ... }
                // TODO make it more precise
                } elseif (false !== strpos($adaptedQuery, 'with')
                    && false !== strpos($adaptedQuery, 'delete')) {
                    return 'withDelete';

                // check if query is of type: DELETE { ... } WHERE { ... }
                // TODO make it more precise
                } elseif (false !== strpos($adaptedQuery, 'delete')
                    && false !== strpos($adaptedQuery, 'where')) {
                    return 'deletePrologWhere';
                }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getQueryParts()
    {
        $queryFromDelete = substr($this->getQuery(), strpos($this->getQuery(), 'DELETE'));

        $this->queryParts = [
            'graphs' => $this->extractGraphs($this->getQuery()),
            'sub_type' => $this->getSubType(),
        ];
        $tripleRelatedPart = $this->getQuery();

        /*
         * Save parts for INSERT DATA
         */
        if ('insertData' === $this->queryParts['sub_type']) {
            preg_match('/INSERT\s+DATA\s+\{\s*(.*)\s*\}/si', $this->getQuery(), $matches);

            if (true === isset($matches[1]) && false === empty($matches[1])) {
                $this->queryParts['insertData'] = trim($matches[1]);
                $this->queryParts['deleteData'] = null;
                $this->queryParts['deleteWhere'] = null;

            /*
             * TODO extract graphs
             */
            } else {
                throw new \Exception('No triple part after INSERT DATA found.');
            }

            /*
             * Save parts for INSERT INTO GRAPH <> {}
             */
        } elseif ('insertInto' === $this->queryParts['sub_type']) {
            preg_match('/INSERT\s+INTO\s+[GRAPH]{0,1}\s*\<(.*)\>\s*\{(.*)\}/si', $this->getQuery(), $matches);

            if (true === isset($matches[1]) && true === isset($matches[2])) {
                // graph
                $this->queryParts['graphs'] = [trim($matches[1])];
                // triples
                $this->queryParts['insertData'] = trim($matches[2]);
            } else {
                throw new \Exception(
                    'There is either no triple part after INSERT INTO GRAPH or no graph set.'
                );
            }

            /*
             * Save parts for DELETE DATA {}
             */
        } elseif ('deleteData' === $this->queryParts['sub_type']) {
            preg_match('/DELETE\s+DATA\s*\{(.*)\}/s', $this->getQuery(), $matches);

            if (true === isset($matches[1])) {
                // triples
                $this->queryParts['deleteData'] = trim($matches[1]);

            /*
             * TODO extract graphs
             */
            } else {
                throw new \Exception('No triple part after DELETE DATA found.');
            }

            /*
             * Save parts for DELETE FROM <> { } WHERE { }
             */
        } elseif ('deleteFromWhere' === $this->queryParts['sub_type']) {
            preg_match('/DELETE\s+FROM\s*\<(.*)\>\s*[WHERE]{0,}\s*\{(.*)\}/si', $this->getQuery(), $matches);

            if (true === isset($matches[1])) {
                // graph
                $this->queryParts['graphs'] = [trim($matches[1])];
                // triples
                $this->queryParts['deleteData'] = trim($matches[2]);
            } else {
                throw new \Exception('No triple part after DELETE FROM <> found.');
            }

            /*
             * Save parts for DELETE WHERE {}
             */
        } elseif ('deleteWhere' === $this->queryParts['sub_type']) {
            preg_match('/DELETE\s+WHERE\s*\{(.*)\}/s', $this->getQuery(), $matches);

            if (true === isset($matches[1])) {
                // matching clause
                $this->queryParts['deleteWhere'] = trim($matches[1]);

            /*
             * TODO extract graphs
             */
            } else {
                throw new \Exception('Where part after DELETE WHERE is empty.');
            }

            /*
             * Save parts for DELETE {} WHERE {}
             */
        } elseif ('deletePrologWhere' === $this->queryParts['sub_type']) {
            // TODO extract graphs
            preg_match('/DELETE\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/im', $this->getQuery(), $matches);

            if (true === isset($matches[1]) && true === isset($matches[2])) {
                $this->queryParts['deleteProlog'] = trim($matches[1]);
                $this->queryParts['deleteWhere'] = trim($matches[2]);
            } else {
                throw new \Exception('Where part after DELETE WHERE is empty.');
            }

            /*
             * Save parts for WITH <> DELETE {} WHERE {}
             */
        } elseif ('withDeleteWhere' === $this->queryParts['sub_type']) {
            preg_match('/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/si', $this->getQuery(), $matches);

            if (true === isset($matches[1])) {
                $this->queryParts['deleteData'] = trim($matches[2]);
                $this->queryParts['deleteWhere'] = trim($matches[3]);
                $this->queryParts['graphs'] = [trim($matches[1])];

                $tripleRelatedPart = $this->queryParts['deleteData'].$this->queryParts['deleteWhere'];

            /*
             * TODO extract graphs
             */
            } else {
                throw new \Exception(
                    'No valid WITH <> DELETE {...} WHERE { ...} query given.'
                );
            }

            /*
             * Save parts for WITH <> DELETE {} INSERT {} WHERE {}
             */
        } elseif ('withDeleteInsertWhere' === $this->queryParts['sub_type']) {
            preg_match(
                '/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*INSERT\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/',
                $this->getQuery(),
                $matches
            );

            if (true === isset($matches[1])) {
                $this->queryParts['deleteData'] = trim($matches[2]);
                $this->queryParts['deleteWhere'] = trim($matches[4]);
                $this->queryParts['insertData'] = trim($matches[3]);

                $this->queryParts['graphs'] = [trim($matches[1])];

            /*
             * TODO extract graphs
             */
            } else {
                throw new \Exception(
                    'No valid WITH <> DELETE {...} INSERT { ... } WHERE { ...} query given.'
                );
            }
        }

        $this->queryParts = array_merge($this->queryParts, [
            'filter_pattern' => $this->extractFilterPattern($this->getQuery()),
            'namespaces' => $this->extractNamespacesFromQuery($queryFromDelete),
            'prefixes' => $this->extractPrefixesFromQuery($this->getQuery()),
            'quad_pattern' => $this->extractQuads($this->getQuery()),
            'triple_pattern' => $this->extractTriplePattern($tripleRelatedPart),
            'variables' => $this->extractVariablesFromQuery($this->getQuery()),
        ]);

        $this->unsetEmptyValues($this->queryParts);

        return $this->queryParts;
    }

    /**
     * Is instance of AskQuery?
     *
     * @return bool False
     */
    public function isAskQuery()
    {
        return false;
    }

    /**
     * Represents it a CONSTRUCT query?
     *
     * @return bool False
     */
    public function isConstructQuery()
    {
        return false;
    }

    /**
     * Is instance of DescribeQuery?
     *
     * @return bool False
     */
    public function isDescribeQuery()
    {
        return false;
    }

    /**
     * Is instance of GraphQuery?
     *
     * @return bool False
     */
    public function isGraphQuery()
    {
        return false;
    }

    /**
     * Is instance of SelectQuery?
     *
     * @return bool False
     */
    public function isSelectQuery()
    {
        return false;
    }

    /**
     * Is instance of UpdateQuery?
     *
     * @return bool True
     */
    public function isUpdateQuery()
    {
        return true;
    }
}
