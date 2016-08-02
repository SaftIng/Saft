<?php

namespace Saft\Skeleton\PropertyHelper;

use Saft\Rdf\NamedNode;
use Saft\Rdf\Statement;
use Saft\Store\Store;

/**
 *
 */
abstract class AbstractIndex
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var NamedNode
     */
    protected $graph;

    /**
     * @var Store
     */
    protected $store;

    /**
     * This array must be filled in an extended class with relevant properties.
     * For instance array('http://www.w3.org/2000/01/rdf-schema#label') for titlehelper.
     *
     * @var array
     */
    protected $preferedProperties = array();

    /**
     * Default language to fetch titles must be set in an extended class.
     * Value could be: "en", "de", "sp"
     *
     * @var string
     */
    protected $defaultLanguage = "";

    /**
     * @param Cache $cache
     * @param Store $store
     * @param NamedNode $graph
     * @throws \Exception if preferedProperties contains 0 elements.
     */
    public function __construct($cache, Store $store, NamedNode $graph)
    {
        $this->cache = $cache;
        $this->graph = $graph;
        $this->store = $store;
    }

    /**
     *
     */
    public function createIndex()
    {
        $queryResult = $this->store->query(
            'SELECT ?s ?p ?o
               FROM <' . $this->graph->getUri() . '>
              WHERE {
                  ?s ?p ?o .
                  FILTER (?p = <' . implode('> || ?p = <', $this->preferedProperties) . '>)
              }'
        );

        $titles = array();

        // fetch result and create the titles
        foreach ($queryResult as $entry) {
            // decide whether $entry is a Statement or an array. That matters in cases where you
            // use Saft's SparqlStore or TriplePatternStore, because both's query method responses
            // different.
            if ($entry instanceof Statement) {
                $subject = $entry->getSubject();
                $predicate = $entry->getPredicate();
                $object = $entry->getObject();
            } else {
                $subject = $entry['s'];
                $predicate = $entry['p'];
                $object = $entry['o'];
            }

            // may titles are not literals? skip
            if (false === $object->isLiteral()) {
                continue;
            }
            $s = (string)$subject;
            $p = (string)$predicate;
            $o = (string)$object;

            $lang = (string)($object->getLanguage());
            if (false === array_key_exists($s, $titles)) {
                $titles[$s] = array('titles' => array());
            }
            $title = array('uri' => $p, 'title' => $o);

            if (null != $lang && false === empty($lang)) {
                $title['lang'] = $lang;
            }
            $titles[$s]['titles'][] = $title;
        }

        // write the cache for each title
        foreach ($titles as $s => $title) {
            // sort title as given range in config->title_uris
            usort($title['titles'], function($a, $b) {
                $aRange = array_search($a['uri'], $this->preferedProperties);
                $bRange = array_search($b['uri'], $this->preferedProperties);
                if ($aRange == $bRange) {
                    return 0;
                }
                return ($aRange < $bRange) ? -1 : 1;
            });
            $this->cache->setItem(md5($s), serialize($title));
        }

        return $titles;
    }

    /**
     * @param array $uriList List of URIs you want property values for
     * @param string $preferedLanguage Prefered language for the fetched titles
     */
    public function fetchValues(array $uriList, $preferedLanguage = "")
    {
        $titles = array();

        foreach ($uriList as $key => $uri) {
            $uriToMd5List[md5($uri)] = $uri;
        }

        if (empty($uriList)) {
            return $titles;
        }

        // get items from cache
        $items = array_map(
            function($title) { return unserialize($title); },
            $this->cache->getItems(array_keys($uriToMd5List))
        );

        foreach ($uriToMd5List as $md5 => $uri) {
            $titleDefLang = null;
            $title = null;

            if ( array_key_exists($md5, $items) ) {
                $titleObjs = $items[$md5];

                foreach ($titleObjs['titles'] as $key => $titleObj) {
                    // language is set for the title
                    if (isset($titleObj['lang'])) {
                        if ($titleObj['lang'] == $preferedLanguage) {
                            $title = $titleObj['title'];
                            break;
                        }
                        if ($titleDefLang == null
                            && $preferedLanguage != $this->defaultLanguage
                            && $titleObj['lang'] == $this->defaultLanguage) {
                            $titleDefLang = $titleObj['title'];
                        }
                    }
                }
                // if a title was found
                if (empty($title)) {
                    if (false === empty($titleDefLang)) {
                        $title = $titleDefLang;
                    } else {
                        $title = array_shift($titleObjs['titles']);
                        $title = $title['title'];
                    }
                }
            }

            $titles[$uri] = $title;
        }

        return $titles;
    }
}
