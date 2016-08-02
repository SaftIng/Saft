<?php

namespace Saft\Skeleton\PropertyHelper;

/**
 *
 */
class TitleHelperIndex extends AbstractIndex
{
    /**
     * This array must be filled in an extended class with relevant properties.
     * For instance array('http://www.w3.org/2000/01/rdf-schema#label') for titlehelper.
     *
     * @var array
     */
    protected $preferedProperties = array(
        'http://purl.org/dc/elements/1.1/title',
        'http://www.w3.org/2000/01/rdf-schema#label',
        'http://purl.org/dc/terms/title',
        'http://purl.org/dc/terms/alternative',
        'http://udfr.org/onto#documentTitle',
        'http://www.w3.org/2004/02/skos/core#prefLabel',
        'http://www.w3.org/2004/02/skos/core#altLabel',
        'http://www.w3.org/2004/02/skos/core#hiddenLabel'
    );

    /**
     * Default language to fetch titles must be set in an extended class.
     * Value could be: "en", "de", "sp" ...
     *
     * @var string
     */
    protected $defaultLanguage = "en";
}
