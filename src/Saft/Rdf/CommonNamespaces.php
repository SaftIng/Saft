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

namespace Saft\Rdf;

class CommonNamespaces
{
    protected $cache;

    protected $namespaces = [
        'bibo' => 'http://purl.org/ontology/bibo/',
        'cc' => 'http://creativecommons.org/ns#',
        'cert' => 'http://www.w3.org/ns/auth/cert#',
        'ctag' => 'http://commontag.org/ns#',
        'dc' => 'http://purl.org/dc/terms/',
        'dc11' => 'http://purl.org/dc/elements/1.1/',
        'dcat' => 'http://www.w3.org/ns/dcat#',
        'dcterms' => 'http://purl.org/dc/terms/',
        'doap' => 'http://usefulinc.com/ns/doap#',
        'exif' => 'http://www.w3.org/2003/12/exif/ns#',
        'foaf' => 'http://xmlns.com/foaf/0.1/',
        'geo' => 'http://www.w3.org/2003/01/geo/',
        'gr' => 'http://purl.org/goodrelations/v1#',
        'grddl' => 'http://www.w3.org/2003/g/data-view#',
        'ical' => 'http://www.w3.org/2002/12/cal/icaltzd#',
        'kno' => 'https://raw.githubusercontent.com/k00ni/knorke/master/knowledge/knorke.ttl#',
        'ma' => 'http://www.w3.org/ns/ma-ont#',
        'og' => 'http://ogp.me/ns#',
        'org' => 'http://www.w3.org/ns/org#',
        'owl' => 'http://www.w3.org/2002/07/owl#',
        'prov' => 'http://www.w3.org/ns/prov#',
        'qb' => 'http://purl.org/linked-data/cube#',
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'rdfa' => 'http://www.w3.org/ns/rdfa#',
        'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
        'rev' => 'http://purl.org/stuff/rev#',
        'rif' => 'http://www.w3.org/2007/rif#',
        'rr' => 'http://www.w3.org/ns/r2rml#',
        'rss' => 'http://purl.org/rss/1.0/',
        'schema' => 'http://schema.org/',
        'sd' => 'http://www.w3.org/ns/sparql-service-description#',
        'sioc' => 'http://rdfs.org/sioc/ns#',
        'sh' => 'http://www.w3.org/ns/shacl#',
        'skos' => 'http://www.w3.org/2004/02/skos/core#',
        'skosxl' => 'http://www.w3.org/2008/05/skos-xl#',
        'synd' => 'http://purl.org/rss/1.0/modules/syndication/',
        'v' => 'http://rdf.data-vocabulary.org/#',
        'vcard' => 'http://www.w3.org/2006/vcard/ns#',
        'void' => 'http://rdfs.org/ns/void#',
        'wdr' => 'http://www.w3.org/2007/05/powder#',
        'wdrs' => 'http://www.w3.org/2007/05/powder-s#',
        'wot' => 'http://xmlns.com/wot/0.1/',
        'xhv' => 'http://www.w3.org/1999/xhtml/vocab#',
        'xml' => 'http://www.w3.org/XML/1998/namespace',
        'xsd' => 'http://www.w3.org/2001/XMLSchema#',
    ];

    /**
     * @param array $customPrefixes Allows you to pass further prefixes on instantiation
     */
    public function __construct(array $customPrefixes = [])
    {
        foreach ($customPrefixes as $prefix => $uri) {
            $this->add($prefix, $uri);
        }

        $this->cache = [];
    }

    /**
     * @param string $prefix
     * @param string $uri
     */
    public function add($prefix, $uri)
    {
        $this->namespaces[$prefix] = $uri;

        $this->cache = [];
    }

    /**
     * @param string $shortenedUri
     *
     * @return string
     */
    public function extendUri($shortenedUri)
    {
        $cacheId = 'extendUri_'.$shortenedUri;

        if (false == isset($this->cache[$cacheId])) {
            $parts = \explode(':', $shortenedUri);

            // exactly 2 parts found. one before and one after the :
            // if namespace is known (by prefix $parts[0])
            if (2 == \count($parts) && isset($this->namespaces[$parts[0]])) {
                $this->cache[$cacheId] = \str_replace($parts[0].':', $this->namespaces[$parts[0]], $shortenedUri);
            } else {
                $this->cache[$cacheId] = $shortenedUri;
            }
        }

        return $this->cache[$cacheId];
    }

    /**
     * @return cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @param string $uriToMatch
     *
     * @return null|string
     */
    public function getPrefix($uriToMatch)
    {
        $cacheId = 'getPrefix_'.$uriToMatch;

        if (false == isset($this->cache[$cacheId])) {
            foreach ($this->namespaces as $prefix => $uri) {
                if ($uriToMatch == $uri) {
                    $this->cache[$cacheId] = $prefix;

                    return $prefix;
                }
            }

            $this->cache[$cacheId] = null;
        }

        return $this->cache[$cacheId];
    }

    public function getUri($prefix)
    {
        return isset($this->namespaces[$prefix]) ? $this->namespaces[$prefix] : null;
    }

    /**
     * Very basic check if a given string is a shortened URI.
     *
     * @param string $string
     *
     * @return bool
     */
    public function isShortenedUri($string)
    {
        return false == \strpos($string, '://') && false !== \strpos($string, ':');
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public function shortenUri($uri)
    {
        $cacheId = 'getPrefix_'.$uri;

        if (false == isset($this->cache[$cacheId])) {
            $longestNamespaceInfo = null;

            foreach ($this->namespaces as $ns => $nsUri) {
                // prefix found
                if (false !== \strpos($uri, $nsUri)) {
                    if (null == $longestNamespaceInfo) {
                        $longestNamespaceInfo = [
                            'ns' => $ns,
                            'nsUri' => $nsUri,
                        ];
                        continue;
                    }

                    // if we found a longer one
                    if (\strlen($nsUri) > \strlen($longestNamespaceInfo['nsUri'])) {
                        $longestNamespaceInfo = [
                            'ns' => $ns,
                            'nsUri' => $nsUri,
                        ];
                    }
                }
            }

            // prefer the prefix with the longest URI to avoid results like foo:bar/fff
            if (null !== $longestNamespaceInfo) {
                $this->cache[$cacheId] = \str_replace($longestNamespaceInfo['nsUri'], $longestNamespaceInfo['ns'].':', $uri);
            } else {
                $this->cache[$cacheId] = $uri;
            }
        }

        return $this->cache[$cacheId];
    }
}
