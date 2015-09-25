<?php

namespace Saft\Rapid;

/**
 * This class maps a given result of a certain resource, class, ... to an instance of itself. With that you
 * are able to access the property values the same way as you use an array.
 *
 * For instance:
 * Lets assume you have the following SetResult.
 *
 * object(Saft\Sparql\Result\SetResultImpl)
 *
 * array (size=2)
 *      'p' =>
 *        object(Saft\Rdf\NamedNodeImpl)
 *          protected 'uri' => string 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
 *      'o' =>
 *        object(Saft\Rdf\NamedNodeImpl)
 *          protected 'uri' => string 'http://www.w3.org/2002/07/owl#Class'
 *
 * Mapping that to an instance of Blank, will lead to:
 *
 *      $blank['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = 'http://www.w3.org/2002/07/owl#Class';
 *
 * To do that just call:
 *
 *      $blank = new Saft\Rapid\Blank($setResult, 'p', 'o');
 *      $blank['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = 'http://www.w3.org/2002/07/owl#Class';
 *
 * Or even with namespaces:
 *
 *      $blank['rdf:type'] = 'http://www.w3.org/2002/07/owl#Class';
 *
 * @api
 * @since 0.1
 * @package Saft\Rapid
 */
class Blank extends \ArrayObject
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
     * Init instance by a given SetResult instance.
     *
     * @param Saft\Sparql\Result\SetResult $result
     * @param string                       $predicate Default: p (optional)
     * @param string                       $object    Default: o (optional)
     * @todo support blank nodes
     */
    public function initBySetResult(\Saft\Sparql\Result\SetResult $result, $predicate = 'p', $object = 'o')
    {
        foreach ($result as $entry) {
            if ($entry[$object]->isNamed()) {
                $value = $entry[$object]->getUri();
            } elseif ($entry[$object]->isLiteral()) {
                $value = $entry[$object]->getValue();
            }

            // set full property URI as key and object value
            $this->setValue($entry[$predicate]->getUri(), $value);

            // furthermore, set namespace shortcut (e.g. rdf) as key and object value, to improve
            // handling later on.
            foreach ($this->commonNamespaces as $ns => $nsUri) {
                if (false !== strpos($entry[$predicate]->getUri(), $nsUri)) {
                    $shorterProperty = str_replace($nsUri, $ns .':', $entry[$predicate]->getUri());
                    $this->setValue($shorterProperty, $value);
                }
            }

            // TODO blank nodes
        }
    }

    /**
     * Init instance by a given StatementIterator instance.
     *
     * @param Saft\Rdf\StatementIterator   $iterator
     * @param string                       $resourceUri URI of the resource to use
     * @param string                       $predicate   Default: p (optional)
     * @param string                       $object      Default: o (optional)
     * @todo support blank nodes
     */
    public function initByStatementIterator(
        \Saft\Rdf\StatementIterator $iterator,
        $resourceUri,
        $predicate = 'p',
        $object = 'o'
    ) {
        // go through given statements
        foreach ($iterator as $statement) {
            // if the current statement has as subject URI the same as the given $resourceUri, integrate
            // its property + value into this instance.
            if ($statement->getSubject()->getUri() == $resourceUri) {
                if ($statement->getObject()->isNamed()) {
                    $value = $statement->getObject()->getUri();
                } elseif ($statement->getObject()->isLiteral()) {
                    $value = $statement->getObject()->getValue();
                }

                // set full property URI as key and object value
                $this->setValue($statement->getPredicate()->getUri(), $value);

                // furthermore, set namespace shortcut (e.g. rdf) as key and object value, to improve
                // handling later on.
                foreach ($this->commonNamespaces as $ns => $nsUri) {
                    if (false !== strpos($statement->getPredicate()->getUri(), $nsUri)) {
                        $shorterProperty = str_replace($nsUri, $ns .':', $statement->getPredicate()->getUri());
                        $this->setValue($shorterProperty, $value);
                    }
                }

                // TODO blank nodes
            }
        }
    }

    /**
     * Helps setting values, but checking if key is already in use. If so, change value to array so that mulitple
     * values for the same key can be stored.
     */
    protected function setValue($key, $value)
    {
        // value already set
        if (isset($this[$key])) {
            // is already an array, add further item
            if (is_array($this[$key])) {
                $this[$key][] = $value;
            // is a string, make it to array
            } else {
                $this[$key] = array($this[$key], $value);
            }
        // value not set already
        } else {
            $this[$key] = $value;
        }
    }
}
