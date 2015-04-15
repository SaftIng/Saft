<?php

namespace Saft\Sparql\Query;

use Saft\Sparql\Query\AbstractQuery;

/**
 * Represents the following types of SPARQL queries:
 * - INSERT DATA
 * - INSERT INTO GRAPH
 * - DELETE DATA
 * - WITH ... DELETE ... WHERE
 * - WITH ... DELETE ... INSERT ... WHERE
 */
class UpdateQuery extends AbstractQuery
{
    /**
     *
     * @param string $query
     * @return string|null
     */
    public function determineSubType($query)
    {
        /**
         * First we get rid of all PREFIX information
         */
        $adaptedQuery = preg_replace('/PREFIX\s+[a-z0-9]+\:\s*\<[a-z0-9\:\/\.\#\-]+\>/', '', $query);
        
        // remove trailing whitespaces
        $adaptedQuery = trim($adaptedQuery);
        
        // only lower chars
        $adaptedQuery = strtolower($adaptedQuery);
        
        $firstPart = substr($adaptedQuery, 0, 8);
              
        // TODO make check more precise, because its possible that are more than one whitespace between keywords.
        switch($firstPart) {
            // DELETE DATA
            case 'delete d':
                return 'deleteData';
            
            // INSERT DATA
            case 'insert d':
                return 'insertData';
            
            // INSERT INTO
            case 'insert i':
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
                }
        }
        
        return null;
    }
    
    /**
     *
     * @return array
     */
    public function getQueryParts()
    {
        $queryFromDelete = substr($this->getQuery(), strpos($this->getQuery(), 'DELETE'));
        
        $this->queryParts = array(
            'filter_pattern' => $this->extractFilterPattern($this->getQuery()),
            'graphs' => $this->extractGraphs($this->getQuery()),
            'namespaces' => $this->extractNamespacesFromQuery($queryFromDelete),
            'prefixes' => $this->extractPrefixesFromQuery($this->getQuery()),
            'sub_type' => $this->determineSubType($this->getQuery()),
            'triple_pattern' => $this->extractTriplePattern($this->getQuery()),
            'variables' => $this->extractVariablesFromQuery($this->getQuery())
        );
        
        /**
         * Save parts for INSERT DATA
         */
        if ('insertData' === $this->queryParts['sub_type']) {
            preg_match('/INSERT\s+DATA\s+\{(.*)\}/', $this->getQuery(), $matches);
            
            if (true === isset($matches[1])) {
                $this->queryParts['insertData'] = trim($matches[1]);
                $this->queryParts['deleteData'] = null;
                $this->queryParts['deleteWhere'] = null;
                
                /**
                 * TODO extract graphs
                 */
            } else {
                throw new \Exception('No triple part after INSERT DATA found.');
            }
            
        /**
         * Save parts for INSERT INTO GRAPH <> {}
         */
        } elseif ('insertInto' === $this->queryParts['sub_type']) {
            preg_match('/INSERT\s+INTO\s+GRAPH\+\<(.*)\>\{(.*)\}/', $this->getQuery(), $matches);
            
            if (true === isset($matches[1]) && true === isset($matches[2])) {
                // graph
                $this->queryParts['graphs'] = array(trim($matches[1]));
                // triples
                $this->queryParts['insertData'] = trim($matches[2]);
            } else {
                throw new \Exception(
                    'There is either no triple part after INSERT INTO GRAPH or no graph set.'
                );
            }
            
        /**
         * Save parts for DELETE DATA {}
         */
        } elseif ('deleteData' === $this->queryParts['sub_type']) {
            preg_match('/DELETE\s+DATA\s*\{(.*)\}/', $this->getQuery(), $matches);
            
            if (true === isset($matches[1])) {
                // triples
                $this->queryParts['deleteData'] = trim($matches[1]);
                
                /**
                 * TODO extract graphs
                 */
            } else {
                throw new \Exception('No triple part after DELETE DATA found.');
            }
            
        /**
         * Save parts for WITH <> DELETE {} WHERE {}
         */
        } elseif ('withDeleteWhere' === $this->queryParts['sub_type']) {
            preg_match('/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/', $this->getQuery(), $matches);
            
            if (true === isset($matches[1])) {
                $this->queryParts['deleteData'] = trim($matches[2]);
                $this->queryParts['deleteWhere'] = trim($matches[3]);
                $this->queryParts['graphs'] = array(trim($matches[1]));
                
                /**
                 * TODO extract graphs
                 */
            } else {
                throw new \Exception(
                    'No valid WITH <> DELETE {...} WHERE { ...} query given.'
                );
            }
            
        /**
         * Save parts for WITH <> DELETE {} INSERT {} WHERE {}
         */
        } elseif ('withDeleteWhere' === $this->queryParts['sub_type']) {
            preg_match(
                '/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*INSERT\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/',
                $this->getQuery(),
                $matches
            );
            
            if (true === isset($matches[1])) {
                $this->queryParts['deleteData'] = trim($matches[2]);
                $this->queryParts['deleteWhere'] = trim($matches[4]);
                $this->queryParts['insertData'] = trim($matches[3]);
                
                $this->queryParts['graphs'] = array(trim($matches[1]));
                
                /**
                 * TODO extract graphs
                 */
            } else {
                throw new \Exception(
                    'No valid WITH <> DELETE {...} INSERT { ... } WHERE { ...} query given.'
                );
            }
        }
        
        $this->unsetEmptyValues($this->queryParts);

        return $this->queryParts;
    }
    
    /**
     * Init the query instance with a given SPARQL query string.
     *
     * @param string $query Query to use for initialization.
     */
    public function init($query)
    {
        $subType = $this->determineSubType($query);
        
        if (null !== $subType) {
            $this->query = $query;
            
            /**
             * Save parts for INSERT DATA
             */
            if ('insertData' === $subType) {
                preg_match('/INSERT\s+DATA\s+\{(.*)\}/', $query, $matches);
                
                if (false === isset($matches[1])) {
                    throw new \Exception('No triple part after INSERT DATA found.');
                }
                
            /**
             * Save parts for INSERT INTO GRAPH <> {}
             */
            } elseif ('insertInto' === $subType) {
                preg_match('/INSERT\s+INTO\s+GRAPH\s+\<(.*)\>\s*\{(.*)\}/', $query, $matches);
                
                if (false === isset($matches[1]) || false === isset($matches[2])) {
                    throw new \Exception(
                        'There is either no triple part after INSERT INTO GRAPH or no graph set.'
                    );
                }
                
            /**
             * Save parts for DELETE DATA {}
             */
            } elseif ('deleteData' === $subType) {
                preg_match('/DELETE\s+DATA\s*\{(.*)\}/', $query, $matches);
                
                if (false === isset($matches[1])) {
                    throw new \Exception('No triple part after DELETE DATA found.');
                }
                
            /**
             * Save parts for WITH <> DELETE {} WHERE {}
             */
            } elseif ('withDeleteWhere' === $subType) {
                preg_match('/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/', $query, $matches);
                
                if (false === isset($matches[1])) {
                    throw new \Exception(
                        'No valid WITH <> DELETE {...} WHERE { ...} query given.'
                    );
                }
                
            /**
             * Save parts for WITH <> DELETE {} INSERT {} WHERE {}
             */
            } elseif ('withDeleteWhere' === $subType) {
                preg_match(
                    '/WITH\s*\<(.*)\>\s*DELETE\s*\{(.*)\}\s*INSERT\s*\{(.*)\}\s*WHERE\s*\{(.*)\}/',
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
            throw new \Exception('Given query is not suitable for UpdateQuery: ' . $query);
        }
    }
    
    /**
     * Is instance of AskQuery?
     *
     * @return boolean False
     */
    public function isAskQuery()
    {
        return false;
    }
    
    /**
     * Is instance of DescribeQuery?
     *
     * @return boolean False
     */
    public function isDescribeQuery()
    {
        return false;
    }
    
    /**
     * Is instance of GraphQuery?
     *
     * @return boolean False
     */
    public function isGraphQuery()
    {
        return false;
    }
    
    /**
     * Is instance of SelectQuery?
     *
     * @return boolean False
     */
    public function isSelectQuery()
    {
        return false;
    }
    
    /**
     * Is instance of UpdateQuery?
     *
     * @return boolean True
     */
    public function isUpdateQuery()
    {
        return true;
    }
}
