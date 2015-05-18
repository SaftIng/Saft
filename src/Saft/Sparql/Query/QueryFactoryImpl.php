<?php

namespace Saft\Sparql\Query;

use Dice\Dice;

class QueryFactoryImpl implements QueryFactory
{
    /**
     * Creates an instance of Query based on given query string.
     *
     * @param  string $query SPARQL query string to use for class instantiation.
     * @return Query Instance of Query.
     */
    public function createInstanceByQueryString($query)
    {
        switch($this->getQueryType($query)) {
            case 'askQuery':
                return new AskQuery($query);

            case 'describeQuery':
                return new DescribeQuery($query);

            case 'graphQuery':
                return new GraphQuery($query);

            case 'selectQuery':
                return new SelectQuery($query);

            case 'updateQuery':
                return new UpdateQuery($query);

            default:
                throw new \Exception('Unknown query type: '. $query);
        }
    }

    /**
     * Get type for a given query.
     *
     * @param  string     $query
     * @return string     Type, which is either askQuery, describeQuery, graphQuery, updateQuery or selectQuery
     * @throws \Exception If unknown query type.
     */
    protected function getQueryType($query)
    {
        /*
         * First we get rid of all PREFIX information
         */
        $adaptedQuery = preg_replace('/PREFIX\s+[a-z0-9]+\:\s*\<[a-z0-9\:\/\.\#\-]+\>/', '', $query);

        // remove trailing whitespaces
        $adaptedQuery = trim($adaptedQuery);

        // only lower chars
        $adaptedQuery = strtolower($adaptedQuery);

        /*
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

            /*
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
                        }
                }
        }

        throw new \Exception('Unknown query type: '. $firstPart);
    }
}
