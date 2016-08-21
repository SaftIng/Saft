<?php

namespace Saft\Sparql\Query;

class QueryUtils
{
    /**
     * Get type for a given SPARQL query.
     *
     * @param  string $query
     * @return string Type, which is either askQuery, describeQuery, graphQuery, updateQuery or selectQuery
     * @throws \Exception if unknown query type.
     */
    public static function getQueryType($query)
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
}
